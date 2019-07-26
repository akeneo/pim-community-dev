<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamilyVariant\Query;

use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;

/**
 * @author    Mathias METAYER <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetValuesOfSiblings
{
    /**
     * @return ValueCollectionInterface[]
     */
    public function for(EntityWithFamilyVariantInterface $entity): array;
}
