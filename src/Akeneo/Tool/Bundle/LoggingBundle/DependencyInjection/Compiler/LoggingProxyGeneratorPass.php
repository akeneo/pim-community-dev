<?php


namespace Akeneo\Tool\Bundle\LoggingBundle\DependencyInjection\Compiler;

use Akeneo\Tool\Bundle\LoggingBundle\Domain\Model\AuditLog;

use Akeneo\Tool\Bundle\LoggingBundle\Domain\Service\AuditLogInterceptor;
use Akeneo\Tool\Bundle\LoggingBundle\Domain\Service\InterceptorLoader;
use CG\Core\DefaultNamingStrategy;
use CG\Core\ReflectionUtils;
use CG\Proxy\Enhancer;
use CG\Proxy\InterceptionGenerator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoggingProxyGeneratorPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container)
    {
        $baseCacheDir = $container->getParameterBag()
            ->resolveValue(
                $container->getParameter('proxies.cache_dir'));
        $cacheDir = $baseCacheDir . '/proxies';

        $interceptors = [];

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isAbstract() || is_null($definition->getClass())) {
                continue;
            }
            $class = $this->initReflectionClass($definition, $container);
            if (is_null($class)) {
                continue;
            }

            $classAnnotations = [];
            foreach (ReflectionUtils::getOverrideableMethods($class) as $reflectionMethod) {

                if ('__construct' === $reflectionMethod->name) {
                    continue;
                }

                $reflectionAttributes = $reflectionMethod->getAttributes(AuditLog::class);
                if (empty($reflectionAttributes)) {
                    continue;
                }
                $classAnnotations[$reflectionMethod->getName()] = [AuditLogInterceptor::class ];

            }
            if (empty($classAnnotations)) {
                continue;
            }

            $proxyFilename = $cacheDir . '/' . str_replace('\\', '-', $class->name) . '.php';
            $generator = new InterceptionGenerator();
            $generator->setFilter(function (\ReflectionMethod $method) use ($classAnnotations) {
                return isset($classAnnotations[$method->name]);
            });

            $enhancer = new Enhancer($class, array(), array(
                $generator
            ));
            $enhancer->setNamingStrategy(new DefaultNamingStrategy('EnhancedProxy' . substr(md5($baseCacheDir), 0, 8)));
            $enhancer->writeClass($proxyFilename);
            $definition->setFile($proxyFilename);
            $definition->setClass($enhancer->getClassName($class));
            $definition->addMethodCall('__CGInterception__setLoader', array(
                new Reference(InterceptorLoader::class)
            ));

            $interceptors[$class->getName()] = $classAnnotations;
        }
        $container
            ->getDefinition(InterceptorLoader::class)
            ->addArgument($interceptors);
    }

    protected function isSymfonyCommand(\ReflectionClass $class): bool
    {
        return $this->isChildOf($class, \Symfony\Component\Console\Command\Command::class);
    }

    protected function initReflectionClass(mixed $definition, ContainerBuilder $container): ?\ReflectionClass
    {
        $className = $definition->getClass();
        if (preg_match('/^%(.*)%$/', $className, $matches)) {
            $className = $container->getParameter($matches[1]);
        }
        try {
            return new \ReflectionClass($className);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function isChildOf(\ReflectionClass $class, string $expectedClass): bool
    {
        /** @var \ReflectionClass $tmpClass */
        while ($tmpClass = $class->getParentClass()) {
            if ($tmpClass->name == $expectedClass)
                return true;
        }
        return false;
    }

    private function relativizePath($targetPath, $path)
    {
        $commonPath = dirname($targetPath);

        $level = 0;
        while (!empty($commonPath)) {
            if (0 === strpos($path, $commonPath)) {
                $relativePath = str_repeat('../', $level) . substr($path, strlen($commonPath) + 1);

                return $relativePath;
            }

            $commonPath = dirname($commonPath);
            $level += 1;
        }

        return $path;
    }

}