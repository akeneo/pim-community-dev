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

namespace PimEnterprise\Bundle\WorkflowBundle\tests\integration\Storage\Sql;

use Akeneo\Test\Integration\TestCase;
use PimEnterprise\Component\Workflow\Model\EntityWithValuesDraftInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SelectModelProposalIdsFromProductModelIdsQueryIntegration extends TestCase
{
    public function testSelectModelProposalIdsFromProductModelIds()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('amor');

        // Update the product model
        $productModelUpdater = $this->get('pim_catalog.updater.product_model');
        $productModelUpdater->update(
            $productModel,
            [
                'values' => [
                    'care_instructions' => [
                        ['data' => 'Do not wash', 'locale' => null, 'scope' => null]
                    ]
                ]
            ]
        );

        // Create a draft of the product model
        $draft = $this->get('pimee_workflow.product_model.builder.draft')->build($productModel, 'julia');
        $draft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
        $draft->markAsReady();

        // Save the draft
        $this->get('pimee_workflow.saver.product_model_draft')->save($draft);

        $query = $this->get('pimee_workflow.query.select_model_proposal_ids_from_product_model_ids');
        $resultRows = $query->fetch([$productModel->getId()]);

        $this->assertCount(1, $resultRows);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
