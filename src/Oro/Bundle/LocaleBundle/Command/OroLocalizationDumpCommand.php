<?php

namespace Oro\Bundle\LocaleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class OroLocalizationDumpCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('oro:localization:dump')
            ->setDescription('Dumps oro js-localization');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetDir = realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../web') . '/js';
        /** @var LocaleSettings $localeSettings */
        $localeSettings = $this->getContainer()->get('oro_locale.settings');
        $addressFormats = $this->getAddressFormats($localeSettings);
        $localeSettingsData = array(
            'locale_data' => $localeSettings->getLocaleData(),
            'currency_data' => $localeSettings->getCurrencyData(),
            'format' => array(
                'address' => $addressFormats,
                'name' => $localeSettings->getNameFormats()
            )
        );

        $file = $targetDir . '/oro.locale_data.js';
        $output->writeln(
            sprintf(
                '<comment>%s</comment> <info>[file+]</info> %s',
                date('H:i:s'),
                basename($file)
            )
        );

        $content = 'define(' . json_encode($localeSettingsData) . ');';
        $this->getContainer()->get('filesystem')->mkdir(dirname($file), 0777);
        if (false === @file_put_contents($file, $content)) {
            throw new \RuntimeException('Unable to write file ' . $file);
        }
    }

    /**
     * Get address formats converted to simplified structure.
     *
     * @param LocaleSettings $localeSettings
     * @return array
     */
    protected function getAddressFormats(LocaleSettings $localeSettings)
    {
        $result = array();
        $formats = $localeSettings->getAddressFormats();
        foreach ($formats as $country => $formatData) {
            $result[$country] = $formatData[LocaleSettings::ADDRESS_FORMAT_KEY];
        }
        return $result;
    }
}
