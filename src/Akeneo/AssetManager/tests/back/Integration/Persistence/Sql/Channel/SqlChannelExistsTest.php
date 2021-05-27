<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Channel;

use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Query\Channel\ChannelExistsInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlChannelExistsTest extends SqlIntegrationTestCase
{
    private ChannelExistsInterface $channelExist;

    public function setUp(): void
    {
        parent::setUp();

        $this->channelExist = $this->get('akeneo_assetmanager.infrastructure.persistence.query.channel_exists');
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_returns_true_if_the_channel_exists()
    {
        $this->assertTrue($this->channelExist->exists(ChannelIdentifier::fromCode('ecommerce')));
    }

    /**
     * @test
     */
    public function it_returns_false_if_the_channel_does_not_exist()
    {
        $this->assertFalse($this->channelExist->exists(ChannelIdentifier::fromCode('wrong_channel')));
    }
}
