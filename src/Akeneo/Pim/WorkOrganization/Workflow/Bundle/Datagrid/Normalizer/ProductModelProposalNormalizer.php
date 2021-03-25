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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;

/**
 * Proposal product model normalizer for datagrid
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class ProductModelProposalNormalizer extends AbstractProposalNormalizer
{
    public function normalize($proposalProduct, $format = null, array $context = []): array
    {
        $result = parent::normalize($proposalProduct, $format, $context);
        $result['document_type'] = 'product_model_draft';
        $result['id'] = 'product_model_draft_' . (string) $proposalProduct->getId();

        return $result;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelDraft && 'datagrid' === $format;
    }
}
