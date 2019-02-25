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

namespace Akeneo\ReferenceEntity\Integration\Persistence\Sql\Channel;

use Akeneo\ReferenceEntity\Domain\Model\ChannelIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Channel\ChannelExistsInterface;
use Akeneo\ReferenceEntity\Integration\SqlIntegrationTestCase;

class SqlChannelExistsTest extends SqlIntegrationTestCase
{
    /** @var ChannelExistsInterface */
    private $channelExist;

    public function setUp(): void
    {
        parent::setUp();

        $this->channelExist = $this->get('akeneo_referenceentity.infrastructure.persistence.query.channel_exists');
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_returns_true_if_the_channel_exists()
    {
        $this->assertTrue(($this->channelExist)(ChannelIdentifier::fromCode('ecommerce')));
    }

    /**
     * @test
     */
    public function it_returns_false_if_the_channel_does_not_exist()
    {
        $this->assertFalse(($this->channelExist)(ChannelIdentifier::fromCode('wrong_channel')));
    }
}
