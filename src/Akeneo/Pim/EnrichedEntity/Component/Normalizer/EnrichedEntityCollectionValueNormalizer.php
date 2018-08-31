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

namespace Akeneo\Pim\EnrichedEntity\Component\Normalizer;

use Akeneo\EnrichedEntity\Domain\Model\Record\Record;
use Akeneo\Pim\EnrichedEntity\Component\Value\EnrichedEntityCollectionValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EnrichedEntityCollectionValueNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface
{
    private $supportedFormats = ['indexing_product', 'indexing_product_and_product_model'];

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ValueInterface $value): string
    {
        $records = $value->getData();
        $recordsIdentifier = array_map(function (Record $record) {
            return $record->getIdentifier()->__toString();
        }, $records);

        return implode($recordsIdentifier, ',');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof EnrichedEntityCollectionValue && in_array($format, $this->supportedFormats);
    }
}
