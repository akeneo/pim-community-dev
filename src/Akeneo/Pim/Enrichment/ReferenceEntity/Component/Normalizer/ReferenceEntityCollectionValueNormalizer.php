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

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Akeneo\Pim\Enrichment\ReferenceEntity\Component\Value\ReferenceEntityCollectionValue;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize records in a Reference Entity Collection.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceEntityCollectionValueNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface
{
    private $supportedFormats = ['indexing_product', 'indexing_product_and_product_model'];

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ValueInterface $value): array
    {
        $records = $value->getData();
        $recordsCode = array_map(function (RecordCode $recordCode) {
            return $recordCode->__toString();
        }, $records);

        return $recordsCode;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ReferenceEntityCollectionValue && in_array($format, $this->supportedFormats);
    }
}
