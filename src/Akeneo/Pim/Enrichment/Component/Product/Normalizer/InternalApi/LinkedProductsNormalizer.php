<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Rows;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LinkedProductsNormalizer
{
    /** @var ImageNormalizer */
    private $imageNormalizer;

    public function __construct(ImageNormalizer $imageNormalizer)
    {
        $this->imageNormalizer = $imageNormalizer;
    }

    public function normalize(Rows $rows, string $channelCode, string $localeCode): array
    {
        return array_map(
            function (Row $row) use ($channelCode, $localeCode) {
                return [
                    'id'                             => $row->technicalId(),
                    'identifier'                      => $row->identifier(),
                    'label'                          => $row->label(),
                    'document_type'                  => $row->documentType(),
                    'image'                          => $this->imageNormalizer->normalize($row->image(), $localeCode, $channelCode),
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
