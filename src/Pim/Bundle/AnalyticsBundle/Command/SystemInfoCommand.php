<?php

namespace Pim\Bundle\AnalyticsBundle\Command;

use Pim\Bundle\AnalyticsBundle\Command\Style\SystemInfoStyle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Command to display system information provided by the data collectors.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SystemInfoCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:system:information')
            ->setDescription('Display Akeneo PIM system information');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dataCollector = $this->getContainer()->get('pim_analytics.data_collector.chained');
        $translator    = $this->getContainer()->get('translator');

        $collectedData = $dataCollector->collect('system_info_advanced_report');

        $decorator = new SystemInfoStyle($input, $output);

        $decorator->title($translator->trans('pim_analytics.system_info.title'));
        $decorator->table([], $this->formatCollectedData($collectedData, $translator));
    }

    /**
     * Formats the collected data to be ready to display by the Table component.
     *
     * @param array               $collectedData
     * @param TranslatorInterface $translator
     *
     * @return array
     */
    protected function formatCollectedData(array $collectedData, TranslatorInterface $translator)
    {
        $formattedData = [];

        foreach ($collectedData as $key => $data) {
            if (is_array($data)) {
                $data = implode(",\n", $data);
            }

            if (!empty($formattedData)) {
                $formattedData[] = new TableSeparator();
            }
            $formattedData[] = [$translator->trans('pim_analytics.info_type.'.$key), $data];
        }

        return $formattedData;
    }
}
