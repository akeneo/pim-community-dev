<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Query;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ListAttributesUseableAsColumnInProductGrid
{
    /**
     * @param string   $locale Code of the locale for the translation of the labels
     * @param int|null $userId Context's user id if needed
     *
     * @return array
     */
    public function fetch(string $locale, int $userId = null): array;
}
