<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Bundle\Doctrine\ORM\Repository;


use Akeneo\Test\Integration\TestCase;
use AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Doctrine\ORM\Query\PublishedProductBuilder;

class PublishedAssociationRepositoryIntegration extends TestCase
{

    use PublishedProductBuilder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->get('feature_flags')->enable('published_product');
    }

    /**
     * @test
     */
    public function it_finds_published_association()
    {
        $productA = $this->createPublishedProduct('productA', ['categories' => ['categoryA']]);
        $productB = $this->createPublishedProduct('productB',
            [
                'categories' => ['categoryB'],
                'associations' => ['X_SELL' => ['products' => ['productA']]
                ]]);
        $associationType = $this->get('pim_catalog.repository.association_type')->findOneByIdentifier('X_SELL');
        $publishedAssociation = $this->get('pimee_workflow.repository.published_association')->findOneByTypeAndOwner($associationType->getId(), $productB->getId());
        $this->assertNotNull($publishedAssociation);
    }


    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }


}
