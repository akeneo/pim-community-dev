<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Present product value in readable format
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
interface ProductValuePresenterInterface
{
    /**
     * Indicates whether this presenter supports the provided value
     *
     * @param ProductValueInterface $value
     *
     * @return boolean
     */
    public function supports(ProductValueInterface $value);

    /**
     * Present the provided value
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    public function present(ProductValueInterface $value);
}
