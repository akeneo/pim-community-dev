<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

/**
 * Class VariantGroupType
 *
 * @author    Stephane Chapeau <stephane.chapeau@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupType extends GroupType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_variant_group';
    }
}
