<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Normalizer\Indexing;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizer for a text (simple text) product value
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class TextNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ValueInterface &&
            AttributeTypes::BACKEND_TYPE_TEXT === $data->getAttribute()->getBackendType() &&
            $format === ProductProposalNormalizer::INDEXING_FORMAT_PRODUCT_PROPOSAL_INDEX;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ValueInterface $value)
    {
        return $value->getData();
    }
}

