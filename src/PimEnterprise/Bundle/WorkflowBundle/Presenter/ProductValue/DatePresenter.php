<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Present a date value
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
        return 'pim_catalog_date' === $value->getAttribute()->getAttributeType();
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
