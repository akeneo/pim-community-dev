<?php

namespace Oro\Bundle\EntityExtendBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Symfony\Component\Process\PhpExecutableFinder;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;

use Oro\Bundle\EntityExtendBundle\Exception\RuntimeException;

use Oro\Bundle\EntityExtendBundle\DependencyInjection\Compiler\EntityManagerPass;
use Symfony\Component\Process\Process;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendClassLoadingUtils;

class OroEntityExtendBundle extends Bundle
{
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        ExtendClassLoadingUtils::registerClassLoader($this->kernel->getCacheDir());
    }

    public function boot()
    {
        $this->ensureInitialized();
    }

    public function build(ContainerBuilder $container)
    {
        $this->ensureInitialized();

        $container->addCompilerPass(new EntityManagerPass());
        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createYamlMappingDriver(
                array(
                    ExtendClassLoadingUtils::getEntityCacheDir($this->kernel->getCacheDir()) => 'Extend\Entity'
                )
            )
        );
    }

    private function ensureInitialized()
    {
        $this->ensureDirExists(ExtendClassLoadingUtils::getEntityCacheDir($this->kernel->getCacheDir()));
        $this->ensureCacheInitialized();
        $this->ensureAliasesSet();
    }

    private function ensureCacheInitialized()
    {
        $aliasesPath = ExtendClassLoadingUtils::getAliasesPath($this->kernel->getCacheDir());
        if (!$this->isCommandExecuting('oro:entity-extend:dump') && !file_exists($aliasesPath)) {
            // We have to warm up the extend entities cache in separate process
            // to allow this process continue executing.
            // The problem is we need initialized DI contained for warming up this cache,
            // but in this moment we are exactly doing this for the current process.
            $console = escapeshellarg($this->getPhp()) . ' ' . escapeshellarg($this->kernel->getRootDir() . '/console');
            $env     = $this->kernel->getEnvironment();
            $process = new Process($console . ' oro:entity-extend:dump' . ' --env ' . $env);
            $process->run();
        }
    }

    private function ensureAliasesSet()
    {
        if (!$this->isCommandExecuting('oro:entity-extend:update-config')) {
            ExtendClassLoadingUtils::setAliases($this->kernel->getCacheDir());
        }
    }

    private function getPhp()
    {
        $phpFinder = new PhpExecutableFinder();
        if (!$phpPath = $phpFinder->find()) {
            throw new \RuntimeException(
                'The php executable could not be found, add it to your PATH environment variable and try again'
            );
        }

        return $phpPath;
    }

    /**
     * Checks if directory exists and attempts to create it if it doesn't exist.
     *
     * @param string $dir
     * @throws RuntimeException
     */
    private function ensureDirExists($dir)
    {
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                throw new RuntimeException(sprintf('Could not create cache directory "%s".', $dir));
            }
        }
    }

    /**
     * Indicates if the given command is being executed.
     *
     * @param string $commandName
     * @return bool
     */
    private function isCommandExecuting($commandName)
    {
        return isset($_SERVER['argv']) && in_array($commandName, $_SERVER['argv']);
    }
}
