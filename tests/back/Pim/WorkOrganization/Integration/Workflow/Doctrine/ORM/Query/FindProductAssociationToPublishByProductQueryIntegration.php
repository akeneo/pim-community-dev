<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Bundle\Doctrine\ORM\Query;

use Akeneo\Test\Integration\Configuration;
use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Doctrine\ORM\Query\PublishedProductBuilder;
use Akeneo\Test\Integration\TestCase;

class FindProductAssociationToPublishByProductQueryIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->get('feature_flags')->enable('published_product');
    }

    use PublishedProductBuilder;


    /**
     * @test
     */
    public function it_finds_association_to_publish()
    {
        $productA = $this->createProduct('productA', ['categories' => ['categoryA']]);
        $productB = $this->createPublishedProduct('productB',
            [
                'categories' => ['categoryB'],
                'associations' => ['X_SELL' => ['products' => ['productA']]
                ]]);

        $results = $this->get("Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Query\FindProductAssociationToPublishByProductQuery")
            ->execute($productA);
        self::assertEquals(1, sizeof($results));
        self::assertEquals(1, $results[0]['product_id']);
        $associationType = $this->get('pim_catalog.repository.association_type')->findOneByIdentifier('X_SELL');

        self::assertEquals($associationType->getId(), $results[0]['association_type_id']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

}
