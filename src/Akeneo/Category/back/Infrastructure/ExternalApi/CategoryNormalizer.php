<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\ExternalApi;

use Akeneo\Category\API\Query\Category;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\DateTimeNormalizer;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoryNormalizer
{
    public function __construct(
        private DateTimeNormalizer $dateTimeNormalizer,
    ) {
    }

    public function normalize(Category $category): array
    {
        return [
            'code' => $category->getCode(),
            'parent' => $category->getParentCode(),
            'updated' => $this->dateTimeNormalizer->normalize($category->getUpdated(), 'standard'),
            'labels' => empty($category->getLabels()) ? (object)[] : $category->getLabels()
        ];
    }
}
