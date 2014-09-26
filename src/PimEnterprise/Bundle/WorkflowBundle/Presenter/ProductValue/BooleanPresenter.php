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
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAware;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAwareInterface;

/**
 * Present a boolean value
 *
 * @author Filips Alpe <filips@akeneo.com>
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
