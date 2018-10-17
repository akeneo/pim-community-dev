<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Category\Query;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;

/**
 * Query data to get the ascendant categories of entities with family variant
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AscendantCategoriesInterface
{
    /**
     * Returns the ids of categories of all ascendant
     *
     * @param EntityWithFamilyVariantInterface $entity
     *
     * @return integer[]
     */
    public function getCategoryIds(EntityWithFamilyVariantInterface $entity): array;
}
