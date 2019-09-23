<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Command;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Service\VolumeAggregation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Aggregate the result of all the volume queries that should not be executed live.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AggregateVolumesCommand extends Command
{
    protected static $defaultName = 'pim:volume:aggregate';

    /** @var VolumeAggregation */
    private $volumeAggregation;

    public function __construct(VolumeAggregation $volumeAggregation)
    {
        parent::__construct();

        $this->volumeAggregation = $volumeAggregation;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Aggregate the result of all the volume queries that should not be executed live');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Aggregation in progress. It can take minutes or hours depending on the size of the catalog.');

        $this->volumeAggregation->aggregate();

        $output->writeln('Catalog volumes aggregation done.');
    }
}
