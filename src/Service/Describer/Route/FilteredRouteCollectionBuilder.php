<?php

namespace Elenyum\OpenAPI\Service\Describer\Route;

use Doctrine\Common\Annotations\Reader;
use Elenyum\OpenAPI\Attribute\Tag;
use Elenyum\OpenAPI\Service\Util\ControllerReflector;
use OpenApi\Annotations\AbstractAnnotation;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class FilteredRouteCollectionBuilder
{
    /** @var Reader|null */
    private ?Reader $annotationReader;

    /** @var ControllerReflector */
    private ControllerReflector $controllerReflector;

    /** @var array */
    private array $options;

    public function __construct(
        ?Reader $annotationReader,
        ControllerReflector $controllerReflector,
        array $options = []
    ) {
        $options = [
            'path_patterns' => $options['path_patterns'],
            'host_patterns' => $options['host_patterns'],
            'name_patterns' => $options['name_patterns'],
            'with_tag' => $options['with_tag'],
            'disable_default_routes' => $options['disable_default_routes'],
        ];

        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'path_patterns' => [],
                'host_patterns' => [],
                'name_patterns' => [],
                'with_tag' => true,
                'disable_default_routes' => false
            ])
            ->setAllowedTypes('path_patterns', 'string[]')
            ->setAllowedTypes('host_patterns', 'string[]')
            ->setAllowedTypes('name_patterns', 'string[]')
            ->setAllowedTypes('with_tag', 'boolean')
            ->setAllowedTypes('disable_default_routes', 'boolean')
        ;

        $this->annotationReader = $annotationReader;
        $this->controllerReflector = $controllerReflector;
        $this->options = $resolver->resolve($options);
    }

    public function filter(RouteCollection $routes): RouteCollection
    {
        $filteredRoutes = new RouteCollection();
        foreach ($routes->all() as $name => $route) {
            if ($this->matchPath($route)
                && $this->matchHost($route)
                && $this->matchTag($route)
                && $this->matchName($name)
                && $this->defaultRouteDisabled($route)
            ) {
                $filteredRoutes->add($name, $route);
            }
        }

        return $filteredRoutes;
    }

    private function matchPath(Route $route): bool
    {
        foreach ($this->options['path_patterns'] as $pathPattern) {
            if (preg_match('{'.$pathPattern.'}', $route->getPath())) {
                return true;
            }
        }

        return 0 === count($this->options['path_patterns']);
    }

    private function matchHost(Route $route): bool
    {
        foreach ($this->options['host_patterns'] as $hostPattern) {
            if (preg_match('{'.$hostPattern.'}', $route->getHost())) {
                return true;
            }
        }

        return 0 === count($this->options['host_patterns']);
    }

    private function matchName(string $name): bool
    {
        foreach ($this->options['name_patterns'] as $namePattern) {
            if (preg_match('{'.$namePattern.'}', $name)) {
                return true;
            }
        }

        return 0 === count($this->options['name_patterns']);
    }

    private function matchTag(Route $route): bool
    {
        if (false === $this->options['with_tag']) {
            return true;
        }

        $reflectionMethod = $this->controllerReflector->getReflectionMethod($route->getDefault('_controller'));

        if (null === $reflectionMethod) {
            return false;
        }

        /** @var Tag|null $module */
        $module = $this->getAttributesAsAnnotation($reflectionMethod, Tag::class)[0] ?? null;

        if (null === $module) {
            /** @var Tag|null $module */
            $module = $this->getAttributesAsAnnotation($reflectionMethod->getDeclaringClass(), Tag::class)[0] ?? null;

            if (null === $module && null !== $this->annotationReader) {
                /** @var Tag|null $module */
                $module = $this->annotationReader->getMethodAnnotation(
                    $reflectionMethod,
                    Tag::class
                );

                if (null === $module) {
                    $module = $this->annotationReader->getClassAnnotation($reflectionMethod->getDeclaringClass(), Tag::class);
                }
            }
        }
        return $module instanceof Tag;
    }

    private function defaultRouteDisabled(Route $route): bool
    {
        if (false === $this->options['disable_default_routes']) {
            return true;
        }

        $method = $this->controllerReflector->getReflectionMethod(
            $route->getDefault('_controller') ?? ''
        );

        if (null === $method) {
            return false;
        }

        $annotations = null !== $this->annotationReader
            ? $this->annotationReader->getMethodAnnotations($method)
            : [];

        if (method_exists(\ReflectionMethod::class, 'getAttributes')) {
            $annotations = array_merge($annotations, array_map(function (\ReflectionAttribute $attribute) {
                return $attribute->newInstance();
            }, $method->getAttributes(AbstractAnnotation::class, \ReflectionAttribute::IS_INSTANCEOF)));
        }

        foreach ($annotations as $annotation) {
            if (false !== strpos(get_class($annotation), 'Elenyum\\OpenAPI\\Attribute')
                || false !== strpos(get_class($annotation), 'OpenApi\\Annotations')
                || false !== strpos(get_class($annotation), 'OpenApi\\Attributes')
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \ReflectionClass|\ReflectionMethod $reflection
     * @param string $className
     * @return array
     */
    private function getAttributesAsAnnotation($reflection, string $className): array
    {
        $annotations = [];
        if (\PHP_VERSION_ID < 80100) {
            return $annotations;
        }

        foreach ($reflection->getAttributes($className, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $annotations[] = $attribute->newInstance();
        }

        return $annotations;
    }
}
