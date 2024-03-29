<?php

namespace Elenyum\OpenAPI\Service;

use Countable;
use Elenyum\OpenAPI\Service\Describer\DescriberInterface;
use Elenyum\OpenAPI\Service\Model\ModelRegistry;
use Elenyum\OpenAPI\Service\OpenApiPhp\ModelRegister;
use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use Psr\Cache\CacheItemPoolInterface;

class ApiDocGenerator
{
    /**
     * @var ?string
     */
    private ?string $group = null;

    /**
     * @param Countable $describers
     * @param ModelRegistry $modelRegistry
     * @param Analysis $analysis
     * @param ModelRegister $modelRegister
     * @param OpenApi $openapi
     * @param CacheItemPoolInterface|null $cacheItemPool
     * @param Generator|null $generator
     * @param array $options
     */
    public function __construct(
        /** @var DescriberInterface[] */
        private Countable $describers,
        private ModelRegistry $modelRegistry,
        private Analysis $analysis,
        private ModelRegister $modelRegister,
        private OpenApi $openapi,
        private ?CacheItemPoolInterface $cacheItemPool,
        private ?Generator $generator = null,
        private array $options = [],
    ) {
    }

    public function generate(): OpenApi
    {
        $group = $this->group;
        if (isset($this->options['cache']['enable']) && $this->options['cache']['enable'] === true && $this->cacheItemPool) {
            $item = $this->cacheItemPool->getItem('elenyum_open_api_'. $group);
            if ($item->isHit()) {
                return $this->openapi = $item->get();
            }
        }

        $context = $this->openapi->_context;
        foreach ($this->describers as $describer) {
            $describer->describe($this->openapi);
        }

        $this->analysis->addAnnotation($this->openapi, $context);

        // Register model annotations
        $modelRegister = $this->modelRegister;
        $groups = $group !== null ? [$group] : null;
        $modelRegister($this->analysis, $groups);

        // Calculate the associated schemas
        $this->modelRegistry->registerSchemas();

        $this->analysis->process($this->generator->getProcessors());
        $this->analysis->validate();

        if (isset($item)) {
            $this->cacheItemPool->save($item->set($this->openapi));
        }

        return $this->openapi;
    }

    public function setGroup(?string $group)
    {
        $this->group = $group;
    }
}
