<?php

namespace Oro\Bundle\NotificationBundle\DependencyInjection\Compiler;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class TemplatesCompilerPass implements CompilerPassInterface
{
    const PARAMETER_KEY = 'oro_notification.emailnotification.templates_list';
    const DIR_NAME      = 'emails';

    /**
     * @var BundleInterface[]
     */
    private $bundles;

    public function __construct($bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $templates = array();
        /** @var Bundle $bundle */
        foreach ($this->bundles as $bundle) {
            $path = $bundle->getPath();

            $dirPath = $path . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . self::DIR_NAME;
            if (is_dir($dirPath)) {
                $finder = new Finder();
                $files = $finder->files()->in($dirPath);

                /** @var \Symfony\Component\Finder\SplFileInfo $file  */
                foreach ($files as $file) {
                    $uniqueTemplateName = join('', array('@', $bundle->getName(), ':', $file->getFilename()));
                    $templates[$uniqueTemplateName] = array(
                        'name' => $uniqueTemplateName,
                        'path' => $dirPath . DIRECTORY_SEPARATOR . $file->getFilename()
                    );
                }
            }
        }

        $container->setParameter(self::PARAMETER_KEY, $templates);
    }
}
