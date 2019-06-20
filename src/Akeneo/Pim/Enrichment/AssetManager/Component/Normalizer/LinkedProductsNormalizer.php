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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Normalizer;

use Akeneo\Asset\Component\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class LinkedProductsNormalizer
{
    /** @var ImageNormalizer */
    private $imageNormalizer;

    public function __construct(ImageNormalizer $imageNormalizer)
    {
        $this->imageNormalizer = $imageNormalizer;
    }

    public function normalize(Rows $rows, string $localeCode): array
    {
        return array_map(
            function (Row $row) use ($localeCode) {
                return [
                    'id'                             => $row->technicalId(),
                    'identifier'                     => $row->identifier(),
                    'label'                          => $row->label(),
                    'document_type'                  => $row->documentType(),
                    'image'                          => $this->imageNormalizer->normalize($row->image(), $localeCode),
                    'completeness'                   => $row->completeness(),
                    'variant_product_completenesses' => $this->getChildrenCompleteness($row),
                ];
            },
            $rows->rows()
        );
    }

    private function getChildrenCompleteness(Row $row): ?array
    {
        if ($row->documentType() !== 'product_model') {
            return null;
        }

        $childrenCompleteness = $row->childrenCompleteness();
        return [
            'completeChildren' => $childrenCompleteness['complete'],
            'totalChildren'    => $childrenCompleteness['total']
        ];
    }
}
