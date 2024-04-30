<?php

namespace Elenyum\OpenAPI\Service\ModelDescriber\Annotations;

use Doctrine\Common\Annotations\Reader;
use Elenyum\OpenAPI\Service\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Context;
use OpenApi\Generator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @internal
 */
class SymfonyConstraintAnnotationReader
{
    /**
     * @var Reader|null
     */
    private $annotationsReader;

    /**
     * @var OA\Schema
     */
    private $schema;

    /**
     * @var bool
     */
    private $useValidationGroups;

    public function __construct(?Reader $annotationsReader, bool $useValidationGroups = false)
    {
        $this->annotationsReader = $annotationsReader;
        $this->useValidationGroups = $useValidationGroups;
    }

    /**
     * Update the given property and schema with defined Symfony constraints.
     *
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    public function updateProperty($reflection, OA\Property $property, ?array $validationGroups = null): void
    {
        foreach ($this->getAnnotations($property->_context, $reflection, $validationGroups) as $outerAnnotation) {
            $innerAnnotations = $outerAnnotation instanceof Assert\Compound || $outerAnnotation instanceof Assert\Sequentially
                ? $outerAnnotation->constraints
                : [$outerAnnotation];

            $this->processPropertyAnnotations($reflection, $property, $innerAnnotations);
        }
    }

    private function notNull(Constraint $annotation, OA\Property $property): bool
    {
        if ($annotation instanceof Assert\NotBlank && \property_exists($annotation, 'allowNull') && $annotation->allowNull) {
            // The field is optional
            return false;
        }

        // The field is required
        if (null === $this->schema) {
            return false;
        }

        $propertyName = Util::getSchemaPropertyName($this->schema, $property);
        if (null === $propertyName) {
            return false;
        }

        $existingRequiredFields = Generator::UNDEFINED !== $this->schema->required ? $this->schema->required : [];
        $existingRequiredFields[] = $propertyName;

        $this->schema->required = array_values(array_unique($existingRequiredFields));
        return true;
    }

    private function processPropertyAnnotations($reflection, OA\Property $property, $annotations)
    {
        $map = [
            Assert\NotBlank::class => function (Constraint $annotation) use ($property) {
                return $this->notNull($annotation, $property);
            },
            Assert\NotNull::class => function (Constraint $annotation) use ($property) {
                return $this->notNull($annotation, $property);
            },
            Assert\Length::class => function (Constraint $annotation) use ($property) {
                if (isset($annotation->min)) {
                    $property->minLength = (int) $annotation->min;
                }
                if (isset($annotation->max)) {
                    $property->maxLength = (int) $annotation->max;
                }
            },
            Assert\Regex::class => function (Constraint $annotation) use ($property) {
                $this->appendPattern($property, $annotation->getHtmlPattern());
            },
            Assert\Count::class => function (Constraint $annotation) use ($property) {
                if (isset($annotation->min)) {
                    $property->minItems = (int) $annotation->min;
                }
                if (isset($annotation->max)) {
                    $property->maxItems = (int) $annotation->max;
                }
            },
            Assert\Choice::class => function (Constraint $annotation) use ($property, $reflection) {
                if(!$annotation instanceof Assert\Choice) {
                    return false;
                }
                $this->applyEnumFromChoiceConstraint($property, $annotation, $reflection);
            },
            Assert\Range::class => function (Constraint $annotation) use ($property) {
                if (\is_int($annotation->min)) {
                    $property->minimum = $annotation->min;
                }
                if (\is_int($annotation->max)) {
                    $property->maximum = $annotation->max;
                }
            },
            Assert\LessThan::class => function (Constraint $annotation) use ($property) {
                if (\is_int($annotation->value)) {
                    $property->exclusiveMaximum = true;
                    $property->maximum = $annotation->value;
                }
            },
            Assert\LessThanOrEqual::class => function (Constraint $annotation) use ($property) {
                if (\is_int($annotation->value)) {
                    $property->maximum = $annotation->value;
                }
            },
            Assert\GreaterThan::class => function (Constraint $annotation) use ($property) {
                if (\is_int($annotation->value)) {
                    $property->exclusiveMinimum = true;
                    $property->minimum = $annotation->value;
                }
            },
            Assert\GreaterThanOrEqual::class => function (Constraint $annotation) use ($property) {
                if (\is_int($annotation->value)) {
                    $property->minimum = $annotation->value;
                }
            },
        ];
        foreach ($annotations as $annotation) {
            $mapClass = $map[get_class($annotation)] ?? null;
            if (!empty($mapClass)) {
                $mapClass($annotation);
            }
        }
    }

    public function setSchema($schema): void
    {
        $this->schema = $schema;
    }

    /**
     * Append the pattern from the constraint to the existing pattern.
     */
    private function appendPattern(OA\Schema $property, $newPattern): void
    {
        if (null === $newPattern) {
            return;
        }
        if (Generator::UNDEFINED !== $property->pattern) {
            $property->pattern = sprintf('%s, %s', $property->pattern, $newPattern);
        } else {
            $property->pattern = $newPattern;
        }
    }

    /**
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    private function applyEnumFromChoiceConstraint(OA\Schema $property, Assert\Choice $choice, $reflection): void
    {
        if ($choice->callback) {
            $enumValues = call_user_func(is_array($choice->callback) ? $choice->callback : [$reflection->class, $choice->callback]);
        } else {
            $enumValues = $choice->choices;
        }

        $setEnumOnThis = $property;
        if ($choice->multiple) {
            $setEnumOnThis = Util::getChild($property, OA\Items::class);
        }

        $setEnumOnThis->enum = array_values($enumValues);
    }

    /**
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    private function getAnnotations(Context $parentContext, $reflection, ?array $validationGroups): iterable
    {
        // To correctly load OA annotations
        $this->setContextFromReflection($parentContext, $reflection);

        foreach ($this->locateAnnotations($reflection) as $annotation) {
            if (!$annotation instanceof Constraint) {
                continue;
            }

            if (!$this->useValidationGroups || $this->isConstraintInGroup($annotation, $validationGroups)) {
                yield $annotation;
            }
        }

        $this->setContext(null);
    }

    /**
     * @param \ReflectionProperty|\ReflectionMethod $reflection
     */
    private function locateAnnotations($reflection): \Traversable
    {
        if (\PHP_VERSION_ID >= 80000 && class_exists(Constraint::class)) {
            foreach ($reflection->getAttributes(Constraint::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                yield $attribute->newInstance();
            }
        }

        if (null !== $this->annotationsReader) {
            if ($reflection instanceof \ReflectionProperty) {
                yield from $this->annotationsReader->getPropertyAnnotations($reflection);
            } elseif ($reflection instanceof \ReflectionMethod) {
                yield from $this->annotationsReader->getMethodAnnotations($reflection);
            }
        }
    }

    /**
     * Check to see if the given constraint is in the provided serialization groups.
     *
     * If no groups are provided the validator would run in the Constraint::DEFAULT_GROUP,
     * and constraints without any `groups` passed to them would be in that same
     * default group. So even with a null $validationGroups passed here there still
     * has to be a check on the default group.
     */
    private function isConstraintInGroup(Constraint $annotation, ?array $validationGroups): bool
    {
        return count(array_intersect(
            $validationGroups ?: [Constraint::DEFAULT_GROUP],
            (array) $annotation->groups
        )) > 0;
    }

    private function setContext(?Context $context): void
    {
        // zircote/swagger-php ^4.0
        \OpenApi\Generator::$context = $context;
    }

    private function setContextFromReflection(Context $parentContext, $reflection): void
    {
        // In order to have nicer errors
        if ($reflection instanceof \ReflectionClass) {
            $this->setContext(Util::createWeakContext($parentContext, [
                'namespace' => $reflection->getNamespaceName(),
                'class' => $reflection->getShortName(),
                'filename' => $reflection->getFileName(),
            ]));
        } else {
            $declaringClass = $reflection->getDeclaringClass();

            $this->setContext(Util::createWeakContext($parentContext, [
                'namespace' => $declaringClass->getNamespaceName(),
                'class' => $declaringClass->getShortName(),
                'property' => $reflection->name,
                'filename' => $declaringClass->getFileName(),
            ]));
        }
    }
}
