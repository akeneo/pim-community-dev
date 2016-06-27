<?php

namespace Pim\Bundle\AnalyticsBundle\Command;

use Pim\Bundle\AnalyticsBundle\Command\Style\SystemInfoStyle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Displays system information provided by the data collectors through command line.
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
            ->setDescription('Displays Akeneo PIM system information');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $translator = $this->getContainer()->get('translator');
        $systemInfoStyle = new SystemInfoStyle($input, $output);

        $systemInfoStyle->title($translator->trans('pim_analytics.system_info.title'));
        $systemInfoStyle->table([], $this->formatCollectedData($translator, $this->getCollectedData()));
    }

    /**
     * Gets all the collected data from the system.
     *
     * @return array
     */
    protected function getCollectedData()
    {
        return $this->getContainer()
            ->get('pim_analytics.data_collector.chained')
            ->collect('system_info_report');
    }

    /**
     * Formats the collected data to be ready to display by the Table component.
     *
     * @param TranslatorInterface $translator
     * @param array               $collectedData
     *
     * @return array
     */
    protected function formatCollectedData(TranslatorInterface $translator, array $collectedData)
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
