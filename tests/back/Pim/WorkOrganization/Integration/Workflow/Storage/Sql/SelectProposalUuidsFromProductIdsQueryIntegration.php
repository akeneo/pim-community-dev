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

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\Sql;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Test\Integration\TestCase;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class SelectProposalUuidsFromProductIdsQueryIntegration extends TestCase
{
    public function testSelectProposalIdsFromProductUuids(): void
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('1111111113');

        // Update the product
        $this->get('pim_catalog.updater.product')->update(
            $product,
            [
                'values' => [
                    'ean' => [
                        ['data' => '986574312', 'locale' => null, 'scope' => null]
                    ]
                ]
            ]
        );

        // Create a draft of the product model
        $user = $this->get('pim_user.provider.user')->loadUserByUsername('julia');
        $draftSource = $this->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')->createFromUser($user);

        $draft = $this->get('pimee_workflow.product.builder.draft')->build($product, $draftSource);
        $draft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
        $draft->markAsReady();

        // Save the draft
        $this->get('pimee_workflow.saver.product_draft')->save($draft);

        $query = $this->get('pimee_workflow.query.select_proposal_ids_from_product_uuids');
        $resultRows = $query->fetch([$product->getUuid()]);

        $this->assertCount(1, $resultRows);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
