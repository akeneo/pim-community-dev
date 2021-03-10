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

    public function test_it_checks_if_a_category_is_linked_to_a_channel(): void
    {
        $category = $this->createCategory(['code' => 'clothes']);
        $this->createChannel([
            'code' => 'mobile',
            'category_tree' => 'clothes',
            'currencies' => ['USD'],
            'locales' => ['en_US']
        ]);

        $isLinked = $this->isCategoryTreeLinkedToChannel->byCategoryTreeId($category->getId());

        $this->assertTrue($isLinked);
    }

    public function test_it_checks_if_a_category_is_not_linked_to_a_channel(): void
    {
        $category = $this->createCategory(['code' => 'clothes']);

        $isLinked = $this->isCategoryTreeLinkedToChannel->byCategoryTreeId($category->getId());

        $this->assertFalse($isLinked);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createChannel(array $data): ChannelInterface
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();
        $this->get('pim_catalog.updater.channel')->update($channel, $data);

        $violations = $this->get('validator')->validate($channel);
        if (count($violations) > 0) {
            throw new \InvalidArgumentException((string)$violations);
        }

        $this->get('pim_catalog.saver.channel')->save($channel);

        return $channel;
    }
}
