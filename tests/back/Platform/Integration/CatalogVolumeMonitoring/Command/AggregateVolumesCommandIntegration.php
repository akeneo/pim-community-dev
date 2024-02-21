<?php

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Command;

use Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\Command\AggregateVolumesCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AggregateVolumesCommandIntegration extends KernelTestCase
{
    public function testAggregateVolumes()
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);

        $command = $application->find('pim:volume:aggregate');

        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('Catalog volumes aggregation done.', $output);
    }
}
