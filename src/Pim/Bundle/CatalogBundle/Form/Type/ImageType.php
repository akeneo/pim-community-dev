<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Pim\Bundle\FlexibleEntityBundle\Form\Type\MediaType;

/**
 * Form type linked to Media entity
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImageType extends MediaType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_image';
    }
}
