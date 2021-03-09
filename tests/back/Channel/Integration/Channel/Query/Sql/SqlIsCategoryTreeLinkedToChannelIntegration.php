<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Channel\Integration\Channel\Query\Sql;

use Akeneo\Channel\Bundle\Query\Sql\SqlIsCategoryTreeLinkedToChannel;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class SqlIsCategoryTreeLinkedToChannelIntegration extends TestCase
{
    private SqlIsCategoryTreeLinkedToChannel $isCategoryTreeLinkedToChannel;

    public function setUp(): void
    {
        parent::setUp();

        $this->isCategoryTreeLinkedToChannel = $this->get('pim_channel.query.sql.is_category_tree_linked_to_channel');
    }

    public function test_it_counts_the_number_channels_linked_to_a_category(): void
    {
        $category = $this->createCategory(['code' => 'clothes']);
        $this->createChannel(['code' => 'mobile', 'category_tree' => 'clothes']);

        $isLinked = $this->isCategoryTreeLinkedToChannel->byCategoryTreeId($category->getId());

        $this->assertTrue($isLinked);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createChannel(array $data): ChannelInterface
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();
        $this->get('pim_catalog.updater.channel')->update($channel, $data);
        $this->get('validator')->validate($channel);
        $this->get('pim_catalog.saver.channel')->save($channel);

        return $channel;
    }
}
