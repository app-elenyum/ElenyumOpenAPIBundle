<?php

namespace Elenyum\OpenAPI\Service\OpenApiPhp;

use Elenyum\OpenAPI\Attribute\Model as ModelAnnotation;
use Elenyum\OpenAPI\Service\Model\Model;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\PropertyInfo\Type;

/**
 * Resolves the path in SwaggerPhp annotation when needed.
 *
 * @internal
 */
class ModelRegister
{
    /** @var ModelRegistry */
    private $modelRegistry;

    public function __construct(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }

    public function __invoke(Analysis $analysis, array $inputGroups = null)
    {
        foreach ($analysis->annotations as $annotation) {
            // @Model using the ref field
            if ($annotation instanceof OA\Schema && $annotation->ref instanceof ModelAnnotation) {
                $model = $annotation->ref;
                $annotation->ref = $this->modelRegistry->register(new Model($this->createType($model->type), $this->getGroups($model, $inputGroups), $model->options, $model->serializationContext));

                // It is no longer an unmerged annotation
                $this->detach($model, $annotation, $analysis);

                continue;
            }

            // Misusage of ::$ref
            if (($annotation instanceof OA\Response || $annotation instanceof OA\RequestBody) && $annotation->ref instanceof ModelAnnotation) {
                throw new \InvalidArgumentException(sprintf('Using @Model inside @%s::$ref is not allowed. You should use ::$ref with @Property, @Parameter, @Schema, @Items but within @Response or @RequestBody you should put @Model directly at the root of the annotation : `@Response(..., @Model(...))`.', get_class($annotation)));
            }

            // Implicit usages

            // We don't use $ref for @Responses, @RequestBody and @Parameter to respect semantics
            // We don't replace these objects with the @Model found (we inject it in a subfield) whereas we do for @Schemas

            $model = $this->getModel($annotation); // We check whether there is a @Model annotation nested
            if (null === $model) {
                continue;
            }

            if ($annotation instanceof OA\Response || $annotation instanceof OA\RequestBody) {
                $properties = [
                    '_context' => Util::createContext(['nested' => $annotation], $annotation->_context),
                    'ref' => $this->modelRegistry->register(new Model($this->createType($model->type), $this->getGroups($model, $inputGroups), $model->options, $model->serializationContext)),
                ];

                $this->createContentForMediaType($properties, $annotation, $analysis);
                $this->detach($model, $annotation, $analysis);

                continue;
            }

            if (!$annotation instanceof OA\Parameter) {
                throw new \InvalidArgumentException(sprintf("@Model annotation can't be nested with an annotation of type @%s.", get_class($annotation)));
            }

            if ($annotation->schema instanceof OA\Schema && 'array' === $annotation->schema->type) {
                $annotationClass = OA\Items::class;
            } else {
                $annotationClass = OA\Schema::class;
            }

            if (!is_string($model->type)) {
                // Ignore invalid annotations, they are validated later
                continue;
            }

            $annotation->merge([new $annotationClass([
                'ref' => $this->modelRegistry->register(new Model($this->createType($model->type), $this->getGroups($model, $inputGroups), $model->options, $model->serializationContext)),
            ])]);

            // It is no longer an unmerged annotation
            $this->detach($model, $annotation, $analysis);
        }
    }

    private function getGroups(ModelAnnotation $model, array $inputGroups = null): ?array
    {
        if (null === $model->groups) {
            return $inputGroups;
        }

        return array_merge($inputGroups ?? [], $model->groups);
    }

    private function detach(ModelAnnotation $model, OA\AbstractAnnotation $annotation, Analysis $analysis): void
    {
        if (Generator::UNDEFINED !== $annotation->attachables) {
            foreach ($annotation->attachables as $key => $attachable) {
                if ($attachable === $model) {
                    unset($annotation->attachables[$key]);

                    break;
                }
            }
        }

        $analysis->annotations->detach($model);
    }

    private function createType(string $type): Type
    {
        if ('[]' === substr($type, -2)) {
            return new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true, null, $this->createType(substr($type, 0, -2)));
        }

        return new Type(Type::BUILTIN_TYPE_OBJECT, false, $type);
    }

    private function getModel(OA\AbstractAnnotation $annotation): ?ModelAnnotation
    {
        if (Generator::UNDEFINED !== $annotation->attachables) {
            foreach ($annotation->attachables as $attachable) {
                if ($attachable instanceof ModelAnnotation) {
                    return $attachable;
                }
            }
        }

        return null;
    }

    private function createContentForMediaType(
        array $properties,
        OA\AbstractAnnotation $annotation,
        Analysis $analysis
    ) {
        $modelAnnotation = new OA\JsonContent($properties);
        $annotation->merge([$modelAnnotation]);
        $analysis->addAnnotation($modelAnnotation, $properties['_context']);
    }
}
