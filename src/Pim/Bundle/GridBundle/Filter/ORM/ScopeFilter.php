<?php

namespace Pim\Bundle\GridBundle\Filter\ORM;

use Oro\Bundle\GridBundle\Filter\ORM\ChoiceFilter;
use Pim\Bundle\FilterBundle\Form\Type\Filter\ScopeFilterType;

/**
 * Overriding of Choice filter
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ScopeFilter extends ChoiceFilter
{
    /**
     * Override apply method to disable filtering apply in query
     *
     * {@inheritdoc}
     */
    public function apply($queryBuilder, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => ScopeFilterType::NAME
        );
    }

    /**
     * {@inheritdoc}
     */
    public function parseData($data)
    {
        return false;
    }
}
