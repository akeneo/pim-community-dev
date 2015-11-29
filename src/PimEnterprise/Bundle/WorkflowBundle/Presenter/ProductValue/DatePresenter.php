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

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Present a date value
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class DatePresenter implements ProductValuePresenterInterface
{
    /** @staticvar string */
    const DATE_FORMAT = 'F d, Y';

    /**
     * {@inheritdoc}
     */
    public function supports(ProductValueInterface $value)
    {
        return AttributeTypes::DATE === $value->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function present(ProductValueInterface $value)
    {
        $data = $value->getData();

        return $data instanceof \DateTime ? $data->format(static::DATE_FORMAT) : '';
    }
}
