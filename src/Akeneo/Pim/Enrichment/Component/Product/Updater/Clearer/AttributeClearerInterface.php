<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;

/**
 * Clears the attribute value of an entity.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeClearerInterface extends ClearerInterface
{
    /**
     * Returns true if the clearer supports the given attribute.
     *
     * @param string $attributeCode
     * @return bool
     */
    public function supportsAttributeCode(string $attributeCode): bool;
}
