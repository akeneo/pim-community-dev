<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

/**
 * Interface CursorFactoryInterface
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CursorFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createProductCursor($queryBuilder);
}
