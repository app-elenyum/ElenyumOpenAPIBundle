<?php

namespace Elenyum\OpenAPI\Service\ModelDescriber;

use Elenyum\OpenAPI\Service\Model\Model;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\ModelDescriber\Annotations\AnnotationsReader;
use Elenyum\OpenAPI\Service\OpenApiPhp\Util;
use Elenyum\OpenAPI\Service\PropertyDescriber\PropertyDescriberInterface;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class ObjectModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{

    /** @var PropertyInfoExtractorInterface */
    private $propertyInfo;
    /** @var ClassMetadataFactoryInterface|null */
    private $classMetadataFactory;
    /** @var Reader|null */
    private $doctrineReader;
    /** @var PropertyDescriberInterface|PropertyDescriberInterface[] */
    private $propertyDescriber;
    /** @var string[] */
    private $mediaTypes;
    /** @var NameConverterInterface|null */
    private $nameConverter;
    /** @var bool */
    private $useValidationGroups;

    /**
     * @param PropertyDescriberInterface|PropertyDescriberInterface[] $propertyDescribers
     */
    public function __construct(
        PropertyInfoExtractorInterface $propertyInfo,
        ?Reader $reader,
        $propertyDescribers,
        array $options,
        NameConverterInterface $nameConverter = null,
        bool $useValidationGroups = false,
        ClassMetadataFactoryInterface $classMetadataFactory = null
    ) {
        $this->propertyInfo = $propertyInfo;
        $this->doctrineReader = $reader;
        $this->propertyDescriber = $propertyDescribers;
        $this->mediaTypes = $options['media_types'];
        $this->nameConverter = $nameConverter;
        $this->useValidationGroups = $useValidationGroups;
        $this->classMetadataFactory = $classMetadataFactory;
    }

    public function describe(Model $model, OA\Schema $schema)
    {
        $class = $model->getType()->getClassName();
        $schema->_context->class = $class;

        $context = ['serializer_groups' => null];
        if (null !== $model->getGroups()) {
            $context['serializer_groups'] = array_filter($model->getGroups(), 'is_string');
        }

        $reflClass = new \ReflectionClass($class);
        $annotationsReader = new AnnotationsReader(
            $this->doctrineReader,
            $this->modelRegistry,
            $this->mediaTypes,
            $this->useValidationGroups
        );
        $classResult = $annotationsReader->updateDefinition($reflClass, $schema);

        if (!$classResult->shouldDescribeModelProperties()) {
            return;
        }

        $schema->type = 'object';

        $mapping = false;
        if (null !== $this->classMetadataFactory) {
            $mapping = $this->classMetadataFactory
                ->getMetadataFor($class)
                ->getClassDiscriminatorMapping();
        }

        if ($mapping && Generator::UNDEFINED === $schema->discriminator) {
            $this->applyOpenApiDiscriminator(
                $model,
                $schema,
                $this->modelRegistry,
                $mapping->getTypeProperty(),
                $mapping->getTypesMapping()
            );
        }

        $propertyInfoProperties = $this->propertyInfo->getProperties($class, $context);

        if (null === $propertyInfoProperties) {
            return;
        }

        // The SerializerExtractor does expose private/protected properties for some reason, so we eliminate them here
        $propertyInfoProperties = array_intersect($propertyInfoProperties, $this->propertyInfo->getProperties($class, []) ?? []);

        $defaultValues = array_filter($reflClass->getDefaultProperties(), static function ($value) {
            return null !== $value;
        });

        foreach ($propertyInfoProperties as $propertyName) {
            $serializedName = null !== $this->nameConverter ? $this->nameConverter->normalize($propertyName, $class, null, $model->getSerializationContext()) : $propertyName;

            $reflections = $this->getReflections($reflClass, $propertyName);

            // Check if a custom name is set
            foreach ($reflections as $reflection) {
                $serializedName = $annotationsReader->getPropertyName($reflection, $serializedName);
            }

            $property = Util::getProperty($schema, $serializedName);

            // Interpret additional options
            $groups = $model->getGroups();
            if (isset($groups[$propertyName]) && is_array($groups[$propertyName])) {
                $groups = $model->getGroups()[$propertyName];
            }
            foreach ($reflections as $reflection) {
                $annotationsReader->updateProperty($reflection, $property, $groups);
            }

            // If type manually defined
            if (Generator::UNDEFINED !== $property->type || Generator::UNDEFINED !== $property->ref) {
                continue;
            }

            if (Generator::UNDEFINED === $property->default && array_key_exists($propertyName, $defaultValues)) {
                $property->default = $defaultValues[$propertyName];
            }

            $types = $this->propertyInfo->getTypes($class, $propertyName);
            if (null === $types || 0 === count($types)) {
                throw new \LogicException(sprintf('The PropertyInfo component was not able to guess the type of %s::$%s. You may need to add a `@var` annotation or use `@OA\Property(type="")` to make its type explicit.', $class, $propertyName));
            }

            $this->describeProperty($types, $model, $property, $propertyName, $schema);
        }
    }

    /**
     * @return \ReflectionProperty[]|\ReflectionMethod[]
     */
    private function getReflections(\ReflectionClass $reflClass, string $propertyName): array
    {
        $reflections = [];
        if ($reflClass->hasProperty($propertyName)) {
            $reflections[] = $reflClass->getProperty($propertyName);
        }

        $camelProp = $this->camelize($propertyName);
        foreach (['', 'get', 'is', 'has', 'can', 'add', 'remove', 'set'] as $prefix) {
            if ($reflClass->hasMethod($prefix.$camelProp)) {
                $reflections[] = $reflClass->getMethod($prefix.$camelProp);
            }
        }

        return $reflections;
    }

    /**
     * Camelizes a given string.
     */
    private function camelize(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    /**
     * @param Type[] $types
     */
    private function describeProperty(array $types, Model $model, OA\Schema $property, string $propertyName, OA\Schema $schema)
    {
        $propertyDescribers = is_iterable($this->propertyDescriber) ? $this->propertyDescriber : [$this->propertyDescriber];

        foreach ($propertyDescribers as $propertyDescriber) {
            if ($propertyDescriber instanceof ModelRegistryAwareInterface) {
                $propertyDescriber->setModelRegistry($this->modelRegistry);
            }
            if ($propertyDescriber->supports($types)) {
                $propertyDescriber->describe($types, $property, $model->getGroups(), $schema, $model->getSerializationContext());

                return;
            }
        }

        throw new \Exception(sprintf('Type "%s" is not supported in %s::$%s. You may use the `@OA\Property(type="")` annotation to specify it manually.', $types[0]->getBuiltinType(), $model->getType()->getClassName(), $propertyName));
    }

    public function supports(Model $model): bool
    {
        return Type::BUILTIN_TYPE_OBJECT === $model->getType()->getBuiltinType()
            && (class_exists($model->getType()->getClassName()) || interface_exists($model->getType()->getClassName()));
    }

    /**
     * @var ModelRegistry
     */
    private $modelRegistry;

    public function setModelRegistry(ModelRegistry $modelRegistry)
    {
        $this->modelRegistry = $modelRegistry;
    }

    /**
     * @param Model                 $model                 the model that's being described, This is used to pass groups and config
     *                                                     down to the children models in `oneOf`
     * @param OA\Schema             $schema                The Open API schema to which `oneOf` and `discriminator` properties
     *                                                     will be added
     * @param string                $discriminatorProperty The property that determine which model will be unsierailized
     * @param array<string, string> $typeMap               the map of $discriminatorProperty values to their
     *                                                     types
     */
    protected function applyOpenApiDiscriminator(
        Model $model,
        OA\Schema $schema,
        ModelRegistry $modelRegistry,
        string $discriminatorProperty,
        array $typeMap
    ): void {
        $weakContext = Util::createWeakContext($schema->_context);

        $schema->oneOf = [];
        $schema->discriminator = new OA\Discriminator(['_context' => $weakContext]);
        $schema->discriminator->propertyName = $discriminatorProperty;
        $schema->discriminator->mapping = [];
        foreach ($typeMap as $propertyValue => $className) {
            $oneOfSchema = new OA\Schema(['_context' => $weakContext]);
            $oneOfSchema->ref = $modelRegistry->register(new Model(
                new Type(Type::BUILTIN_TYPE_OBJECT, false, $className),
                $model->getGroups(),
                $model->getOptions()
            ));
            $schema->oneOf[] = $oneOfSchema;
            $schema->discriminator->mapping[$propertyValue] = $oneOfSchema->ref;
        }
    }
}
