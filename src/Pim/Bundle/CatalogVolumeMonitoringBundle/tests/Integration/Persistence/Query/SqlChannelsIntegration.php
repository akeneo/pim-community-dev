<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\Query;

use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogVolumeMonitoringBundle\tests\Integration\Persistence\BuilderQueryTestCase;

class SqlChannelsIntegration extends BuilderQueryTestCase
{
    public function testGetCountOfChannels()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.channels');
        $this->createChannels(4);

        $volume = $query->fetch();

        //in minimal catalogue we have one channel
        Assert::assertEquals(5, $volume->getVolume());
        Assert::assertEquals('channels', $volume->getVolumeName());
        Assert::assertEquals(false, $volume->hasWarning());
    }

    /**
     * @param int $numberOfChannels
     */
    private function createChannels(int $numberOfChannels): void
    {
        $channels = [];
        $i = 0;

        while ($i < $numberOfChannels) {
            $channel = $this->get('pim_catalog.factory.channel')->create();
            $this->get('pim_catalog.updater.channel')->update(
                $channel,
                [
                    'code'          => 'new_channel_' . rand(),
                    'category_tree' => 'master',
                    'currencies'    => ['USD'],
                    'locales'       => ['fr_FR']
                ]
            );

            $errors = $this->get('validator')->validate($channel);
            Assert::assertCount(0, $errors);
            $channels[] = $channel;
            $i++;
        }
        $this->get('pim_catalog.saver.channel')->saveAll($channels);
    }
}
