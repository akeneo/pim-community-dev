<?php

namespace Pim\Bundle\TransformBundle\Normalizer;

/**
 * A normalizer to transform a category entity into a flat array
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatCategoryNormalizer extends CategoryNormalizer
{
    /**
     * @var array
     */
    protected $supportedFormats = array('csv');
}
