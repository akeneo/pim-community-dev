<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

/**
 * Ui repository interface
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UiRepositoryInterface
{
    /**
     * Get channel choices
     * Allow to list channels in an array like array[<code>] = <label>
     *
     * @return string[]
     */
    public function getLabelsIndexedByCode();
}
