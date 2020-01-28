<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Doctrine\ORM\Query;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectDraftSourceFilterChoicesQueryInterface;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class SelectDraftSourceFilterChoicesQueryIntegration extends TestCase
{
    /**
     * @test
     */
    public function it_returns_an_empty_array_if_there_is_no_draft()
    {
        $sources = $this->getQuery()->execute(null);

        $this->assertEquals([], $sources);
    }

    /**
     * @test
     */
    public function it_returns_all_the_draft_source_filter_choices()
    {
        $this->createProductDraft(new DraftSource('pim', 'PIM', 'mary', 'Mary Smith'));
        $this->createProductDraft(new DraftSource('pim', 'PIM', 'mary', 'Mary Smith'));
        $this->createProductModelDraft(new DraftSource('franklin', 'Franklin Insights', 'franklin_user', 'Franklin user'));

        $sources = $this->getQuery()->execute(null);

        $this->assertEqualsCanonicalizing([
            'PIM' => 'pim',
            'Franklin Insights' => 'franklin',
        ], $sources);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    private function getQuery(): SelectDraftSourceFilterChoicesQueryInterface
    {
        return $this->get('pimee_workflow.query.select_draft_source_filter_choices');
    }

    private function createProductDraft(DraftSource $draftSource): void
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct(Uuid::uuid4()->toString());
        $this->get('pim_catalog.updater.product')->update($product, [
            'categories' => ['categoryA'],
            'values'     => [
                'a_text' => [
                    ['data' => 'a text', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('pim_catalog.updater.product')->update($product, ['values' => [
            'a_text' => [
                ['data' => 'an edited text', 'locale' => null, 'scope' => null]
            ]
        ]]);

        $productDraft = $this->get('pimee_workflow.product.builder.draft')->build($product, $draftSource);
        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);
    }

    private function createProductModelDraft(DraftSource $draftSource): void
    {
        $productModel = $this->get('pim_catalog.factory.product_model')->create();
        $this->get('pim_catalog.updater.product_model')->update(
            $productModel,
            [
                'code'           => Uuid::uuid4()->toString(),
                'family_variant' => 'familyVariantA2',
                'categories'     => ['categoryA'],
                'values'         => [
                    'a_text' => [
                        ['data' => 'a text', 'locale' => null, 'scope' => null]
                    ]
                ]
            ]
        );

        $this->get('pim_catalog.saver.product_model')->save($productModel);
        $this->get('pim_catalog.updater.product_model')->update($productModel, ['values' => [
            'a_text' => [
                ['data' => 'an edited text', 'locale' => null, 'scope' => null]
            ]
        ]]);

        $productModelDraft = $this->get('pimee_workflow.product_model.builder.draft')->build($productModel, $draftSource);

        $this->get('pimee_workflow.saver.product_model_draft')->save($productModelDraft);
    }
}
