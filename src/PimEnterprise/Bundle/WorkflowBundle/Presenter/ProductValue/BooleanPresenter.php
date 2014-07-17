<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue;

use PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAware;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Present a boolean value
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class BooleanPresenter implements ProductValuePresenterInterface, TranslatorAwareInterface
{
    use TranslatorAware;

    /** @staticvar string */
    const YES = 'Yes';

    /** @staticvar string */
    const NO = 'No';

    /**
     * {@inheritdoc}
     */
    public function supports(ProductValueInterface $value)
    {
        return 'pim_catalog_boolean' === $value->getAttribute()->getAttributeType();
    }

    /**
     * {@inheritdoc}
     */
    public function present(ProductValueInterface $value)
    {
        return $this->translator->trans($value->getData() ? self::YES : self::NO);
    }
}
