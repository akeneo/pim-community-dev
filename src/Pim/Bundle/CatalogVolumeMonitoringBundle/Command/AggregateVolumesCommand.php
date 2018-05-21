<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Aggregate the result of all the volume queries that should not be executed live.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AggregateVolumesCommand extends ContainerAwareCommand
{
    private const NAME = 'pim:volume:aggregate';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Aggregate the result of all the volume queries that should not be executed live');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Aggregation in progress. It can take minutes or hours depending on the size of the catalog.');

        $volumeAggregation = $this->getContainer()->get('pim_volume_monitoring.volume.aggregation');
        $volumeAggregation->aggregate();

        $output->writeln('Catalog volumes aggregation done.');
    }
}
