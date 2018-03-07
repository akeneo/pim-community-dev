<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Pim\Bundle\EnrichBundle\Provider\FormExtensionProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dump a form extensions into a file
 *
 * @author Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DumpExtensionsCommand extends ContainerAwareCommand
{
    const MAIN_CONFIG_FILE_NAME = 'test_dist/extensions.json';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:installer:dump-extensions')
            ->setDescription('Dump the form extensions config in the web/test_dist folder');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $extensionProvider = $this->getContainer()->get('pim_enrich.provider.form_extension');

        $output->writeln('Generating form extensions dump');
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $webRoot = realpath($rootDir . '/../web');

        $content = json_encode([
            'extensions'       => $extensionProvider->getExtensions(),
            'attribute_fields' => $extensionProvider->getAttributeFields()
        ]);

        $content = str_replace(',', ",\n", $content);
        $mainConfigFilePath = $webRoot . DIRECTORY_SEPARATOR . self::MAIN_CONFIG_FILE_NAME;
        if (false === file_put_contents($mainConfigFilePath, $content)) {
            throw new \RuntimeException('Unable to write file ' . $mainConfigFilePath);
        }
    }

}
