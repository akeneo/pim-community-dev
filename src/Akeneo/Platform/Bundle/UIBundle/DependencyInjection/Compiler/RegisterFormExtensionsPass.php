<?php

namespace Akeneo\Platform\Bundle\UIBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Compiler pass to load form extension configuration
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterFormExtensionsPass implements CompilerPassInterface
{
    const PROVIDER_ID = 'pim_enrich.provider.form_extension';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::PROVIDER_ID)) {
            return;
        }

        $providerDefinition = $container->getDefinition(static::PROVIDER_ID);

        $extensionConfig = [];
        $attributeFields = [];
        $files = $this->listConfigFiles($container);

        foreach ($files as $file) {
            $config = Yaml::parse(file_get_contents($file->getPathName()));
            if (isset($config['extensions']) && is_array($config['extensions'])) {
                $extensionConfig = array_replace_recursive($extensionConfig, $config['extensions']);
            }
            if (isset($config['attribute_fields']) && is_array($config['attribute_fields'])) {
                $attributeFields = array_merge($attributeFields, $config['attribute_fields']);
            }
            $container->addResource(new FileResource($file->getPathName()));
        }

        foreach ($extensionConfig as $code => $extension) {
            $providerDefinition->addMethodCall('addExtension', [$code, $extension]);
        }

        foreach ($attributeFields as $attributeType => $module) {
            $providerDefinition->addMethodCall('addAttributeField', [$attributeType, $module]);
        }
    }

    /**
     * Get all the form extension configuration files in the Resources/form_extensions/ directories
     *
     * @param ContainerBuilder $container
     *
     * @return \SplFileInfo[]
     */
    protected function listConfigFiles(ContainerBuilder $container)
    {
        $files = [];

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $files = array_merge($files, $this->getConfigurationFiles($bundle, 'Resources/config'));
        }

        return $files;
    }

    private function getConfigurationFiles(string $bundle, string $path): array
    {
        $files = [];
        $reflection = new \ReflectionClass($bundle);
        $directory = sprintf(
            '%s/%s/%s',
            dirname($reflection->getFilename()),
            $path,
            'form_extensions'
        );
        $file = $directory . '.yml';

        if (is_file($file)) {
            $files[] = new \SplFileInfo($file);
        }

        if (is_dir($directory)) {
            $files = array_merge($files, $this->listConfigFilesInDirectory($directory));
        }

        sort($files);

        return $files;
    }

    /**
     * Get the list of configuration files in a given directory.
     *
     * @param string $directory
     *
     * @return \SplFileInfo[]
     */
    protected function listConfigFilesInDirectory($directory)
    {
        $files = [];
        $finder = new Finder();
        $finder->files()->in($directory)->name('*.yml');

        foreach ($finder as $file) {
            $files[] = $file;
        }

        return $files;
    }
}
