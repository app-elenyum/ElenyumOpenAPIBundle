<?php

namespace Elenyum\OpenAPI\Service\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @internal
 */
class ControllerReflector
{
    private $container;

    private $controllers = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns the ReflectionMethod for the given controller string.
     *
     * @return \ReflectionMethod|null
     */
    public function getReflectionMethod($controller)
    {
        if (is_string($controller)) {
            $controller = $this->getClassAndMethod($controller);
        }

        if (null === $controller) {
            return null;
        }

        return $this->geReflectionMethodByClassNameAndMethodName(...$controller);
    }

    /**
     * @return \ReflectionMethod|null
     */
    public function geReflectionMethodByClassNameAndMethodName(string $class, string $method)
    {
        try {
            return new \ReflectionMethod($class, $method);
        } catch (\ReflectionException $e) {
            // In case we can't reflect the controller, we just
            // ignore the route
        }

        return null;
    }

    private function getClassAndMethod(string $controller)
    {
        if (isset($this->controllers[$controller])) {
            return $this->controllers[$controller];
        }

        if (preg_match('#(.+)::([\w]+)#', $controller, $matches)) {
            $class = $matches[1];
            $method = $matches[2];
            // Since symfony 4.1 routes are defined like service_id::method_name
            if (Kernel::VERSION_ID >= 40100 && !class_exists($class)) {
                if ($this->container->has($class)) {
                    $class = get_class($this->container->get($class));
                    if (class_exists(ClassUtils::class)) {
                        $class = ClassUtils::getRealClass($class);
                    }
                }
            }
        } elseif (class_exists($controller)) {
            $class = $controller;
            $method = '__invoke';
        } else {
            // Has to be removed when dropping support of symfony < 4.1
            if (preg_match('#(.+):([\w]+)#', $controller, $matches)) {
                $controller = $matches[1];
                $method = $matches[2];
            }

            if ($this->container->has($controller)) {
                $class = get_class($this->container->get($controller));
                if (class_exists(ClassUtils::class)) {
                    $class = ClassUtils::getRealClass($class);
                }

                if (!isset($method) && method_exists($class, '__invoke')) {
                    $method = '__invoke';
                }
            }
        }

        if (!isset($class) || !isset($method)) {
            $this->controllers[$controller] = null;

            return null;
        }

        return $this->controllers[$controller] = [$class, $method];
    }
}
