<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Storage\Sql;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql\SqlFindPublishedProductId;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

final class FindPublishedProductIdIntegration extends TestCase
{
    private SqlFindPublishedProductId $getPublishedProductId;

    /** @test */
    public function it_gets_the_id_of_a_published_product_from_its_identifier(): void
    {
        $fooId = $this->get('pimee_workflow.repository.published_product')->findOneByIdentifier('foo')->getId();
        Assert::assertSame(
            (string) $fooId,
            $this->getPublishedProductId->fromIdentifier('foo')
        );
        Assert::assertNull($this->getPublishedProductId->fromIdentifier('non_existing_identifier'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->get('feature_flags')->enable('published_product');
        $this->getPublishedProductId = $this->get('pimee_workflow.query.find_published_product_id');
        $productBuilder = $this->get('pim_catalog.builder.product');
        $productFoo = $productBuilder->createProduct('foo');
        $productBar = $productBuilder->createProduct('bar');
        $this->get('pim_catalog.saver.product')->saveAll([$productFoo, $productBar]);
        $this->get('pimee_workflow.manager.published_product')->publish($productFoo);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
