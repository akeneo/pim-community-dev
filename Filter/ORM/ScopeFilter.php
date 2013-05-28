<?php
namespace Pim\Bundle\ProductBundle\Filter\ORM;

use Pim\Bundle\ProductBundle\Form\Type\Filter\ScopeFilterType;

use Oro\Bundle\GridBundle\Filter\ORM\ChoiceFilter;

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
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => ScopeFilterType::NAME
        );
    }
}
