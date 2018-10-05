<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\Query;

use PHPUnit\Framework\Assert;
use AkeneoTest\Platform\Integration\CatalogVolumeMonitoring\Persistence\QueryTestCase;

class CountChannelsIntegration extends QueryTestCase
{
    public function testGetCountOfChannels()
    {
        $query = $this->get('pim_volume_monitoring.persistence.query.count_channels');
        $this->createChannels(4);

        $volume = $query->fetch();

        //in minimal catalogue we have one channel
        Assert::assertEquals(5, $volume->getVolume());
        Assert::assertEquals('count_channels', $volume->getVolumeName());
        Assert::assertEquals(true, $volume->hasWarning());
    }

    /**
     * @param int $numberOfChannels
     */
    private function createChannels(int $numberOfChannels): void
    {
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

            $this->get('pim_catalog.saver.channel')->save($channel);
            $i++;
        }
    }
}
