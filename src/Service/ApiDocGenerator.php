<?php

namespace Elenyum\OpenAPI\Service;

use Elenyum\OpenAPI\Service\Describer\DescriberInterface;
use Elenyum\OpenAPI\Service\Describer\ModelRegistryAwareInterface;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\ModelDescriber\ModelDescriberInterface;
use Elenyum\OpenAPI\Service\OpenApiPhp\ModelRegister;
use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi;
use OpenApi\Context;
use OpenApi\Generator;
use OpenApi\Processors\ProcessorInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareTrait;

class ApiDocGenerator
{
    use LoggerAwareTrait;

    /** @var OpenApi */
    private $openApi;

    /** @var iterable|DescriberInterface[] */
    private $describers;

    /** @var iterable|ModelDescriberInterface[] */
    private $modelDescribers;

    /** @var CacheItemPoolInterface|null */
    private $cacheItemPool;

    /** @var string */
    private $cacheItemId;

    /** @var Generator */
    private $generator;

    /** @var string[] */
    private $alternativeNames = [];

    /** @var string[] */
    private $mediaTypes = ['json'];

    /**
     * @var ?string
     */
    private $openApiVersion = null;

    /**
     * @var ?string
     */
    private ?string $group;

    /**
     * @var array
     */
    private array $options;

    /**
     * @param $describers
     * @param $modelDescribers
     * @param CacheItemPoolInterface|null $cacheItemPool
     * @param string|null $cacheItemId
     * @param Generator|null $generator
     * @param array $options
     */
    public function __construct($describers, $modelDescribers, CacheItemPoolInterface $cacheItemPool = null, string $cacheItemId = null, Generator $generator = null, array $options = [])
    {
        $this->describers = $describers;
        $this->modelDescribers = $modelDescribers;
        $this->cacheItemPool = $cacheItemPool;
        $this->cacheItemId = $cacheItemId ?? 'elenyum_open_api';
        $this->generator = $generator ?? new Generator($this->logger);
        $this->options = $options;
    }

    public function generate(): OpenApi
    {
        if (null !== $this->openApi) {
            return $this->openApi;
        }

        $group = $this->group;
        if (isset($this->options['cache']['enable']) && $this->options['cache']['enable'] === true && $this->cacheItemPool) {
            $item = $this->cacheItemPool->getItem($this->cacheItemId .'_'. $group);
            if ($item->isHit()) {
                return $this->openApi = $item->get();
            }
        }

        if ($this->openApiVersion) {
            $this->generator->setVersion($this->openApiVersion);
        }

        $this->generator->setProcessors($this->getProcessors($this->generator));

        $context = new Context(['version' => $this->generator->getVersion()]);
        $this->openApi = new OpenApi(['_context' => $context]);
        $modelRegistry = new ModelRegistry($this->modelDescribers, $this->openApi, $this->alternativeNames);
        if (null !== $this->logger) {
            $modelRegistry->setLogger($this->logger);
        }
        foreach ($this->describers as $describer) {
            if ($describer instanceof ModelRegistryAwareInterface) {
                $describer->setModelRegistry($modelRegistry);
            }
            $describer->describe($this->openApi);
        }

        $analysis = new Analysis([], $context);
        $analysis->addAnnotation($this->openApi, $context);

        // Register model annotations
        $modelRegister = new ModelRegister($modelRegistry, $this->mediaTypes);
        $groups = $group !== null ? [$group] : null;
        $modelRegister($analysis, $groups);

        // Calculate the associated schemas
        $modelRegistry->registerSchemas();

        $analysis->process($this->generator->getProcessors());
        $analysis->validate();

        if (isset($item)) {
            $this->cacheItemPool->save($item->set($this->openApi));
        }

        return $this->openApi;
    }

    /**
     * Get an array of processors that will be used to process the OpenApi object.
     *
     * @param Generator $generator The generator instance to get the standard processors from
     *
     * @return array<ProcessorInterface|callable> The array of processors
     */
    private function getProcessors(Generator $generator): array
    {
        // Get the standard processors from the generator.
        $processors = $generator->getProcessors();

        // Remove OperationId processor as we use a lot of generated annotations which do not have enough information in their context
        // to generate these ids properly.
        foreach ($processors as $key => $processor) {
            if ($processor instanceof \OpenApi\Processors\OperationId) {
                unset($processors[$key]);
            }
        }

        return $processors;
    }

    public function setGroup(?string $group)
    {
        $this->group = $group;
    }
}
