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

use Akeneo\Asset\Component\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class LinkedProductNormalizer
{
    /** @var ImageNormalizer */
    private $imageNormalizer;

    public function __construct(ImageNormalizer $imageNormalizer)
    {
        $this->imageNormalizer = $imageNormalizer;
    }

    public function normalize(Row $row, string $localeCode): array
    {
        $normalizedProducts = [];
        $normalizedProducts['id'] = $row->technicalId();
        $normalizedProducts['identifier'] = $row->identifier();
        $normalizedProducts['label'] = $row->label();
        $normalizedProducts['document_type'] = $row->documentType();
        $normalizedProducts['image'] = $this->imageNormalizer->normalize($row->image(), $localeCode);
        $normalizedProducts['completeness'] = $row->completeness();
        $normalizedProducts['variant_product_completenesses'] = $this->getChildrenCompleteness($row);

        return $normalizedProducts;
    }

    private function getChildrenCompleteness(Row $row): ?array
    {
        if ($row->documentType() !== 'product_model') {
            return null;
        }

        $childrenCompleteness = $row->childrenCompleteness();
        return [
            'completeChildren' => $childrenCompleteness['complete'],
            'totalChildren' => $childrenCompleteness['total']
        ];
    }
}
