<?php

namespace Pim\Bundle\FilterBundle\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;

/**
 * Completeness filter type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class CompletenessFilterType extends BooleanFilterType
{
    /**
     * @staticvar string
     */
    const NAME = 'pim_type_completeness_filter';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BooleanFilterType::NAME;
    }
}
