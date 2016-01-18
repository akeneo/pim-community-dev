<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Pim\Bundle\CatalogBundle\Model\ChosableInterface;

/**
 * Choices builder interface
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ChoicesBuilderInterface
{
    /**
     * @param ChosableInterface[] $items
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    public function buildChoices($items);
}
