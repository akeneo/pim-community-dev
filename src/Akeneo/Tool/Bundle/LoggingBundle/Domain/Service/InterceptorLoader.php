<?php


namespace Akeneo\Tool\Bundle\LoggingBundle\Domain\Service;

use Akeneo\Tool\Bundle\LoggingBundle\Domain\Model\AttributesBag;
use Akeneo\Tool\Bundle\LoggingBundle\Domain\Model\InterceptorAttributesBag;
use CG\Proxy\InterceptorLoaderInterface;
use CG\Proxy\MethodInterceptorInterface;
use Grpc\Interceptor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InterceptorLoader implements InterceptorLoaderInterface
{
    private $container;
    private $interceptors;
    private $loadedInterceptors = array();

    /**
     * @param ContainerInterface $container
     * @param array<array<string>> $interceptors
     */
    public function __construct(ContainerInterface $container, array $interceptors)
    {
        $this->container = $container;
        $this->interceptors = $interceptors;
    }

    public function loadInterceptors(\ReflectionMethod $method)
    {
        if (!isset($this->interceptors[$method->class][$method->name])) {
            return array();
        }

        if (isset($this->loadedInterceptors[$method->class][$method->name])) {
            return $this->loadedInterceptors[$method->class][$method->name];
        }

        $interceptors = array();
        foreach ($this->interceptors[$method->class][$method->name] as $id) {
            $interceptors[] = $this->container->get($id);
        }

        return $this->loadedInterceptors[$method->class][$method->name] = $interceptors;
    }
}