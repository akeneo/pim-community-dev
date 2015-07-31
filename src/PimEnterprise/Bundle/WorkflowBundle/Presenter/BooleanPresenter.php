<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;

/**
 * Present changes on boolean data
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class BooleanPresenter extends AbstractProductValuePresenter implements TranslatorAwareInterface
{
    use TranslatorAware;

    /** @staticvar string */
    const YES = 'Yes';

    /** @staticvar string */
    const NO = 'No';

    /**
     * {@inheritdoc}
     */
    public function supportsChange($attributeType)
    {
        return AttributeTypes::BOOLEAN === $attributeType;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeData($data)
    {
        return $this->translator->trans($data['data'] ? self::YES : self::NO);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeChange(array $change)
    {
        return $this->translator->trans($change['data'] ? self::YES : self::NO);
    }
}
