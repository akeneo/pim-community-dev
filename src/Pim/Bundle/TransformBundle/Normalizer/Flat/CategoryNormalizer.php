<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\TransformBundle\Normalizer\Structured;

/**
 * A normalizer to transform a category entity into a flat array
 *
 * TODO (2014-07-25 11:30 by Gildas): Couldn't the Structured CategoryNormalizer class could be used here too?
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer extends Structured\CategoryNormalizer
{
    /**
     * @var array
     */
    protected $supportedFormats = array('csv');
}
