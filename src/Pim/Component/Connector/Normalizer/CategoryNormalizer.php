<?php

namespace Pim\Component\Connector\Normalizer;

use Pim\Component\Catalog\Normalizer\CategoryNormalizer as BaseNormalizer;

/**
 * A normalizer to transform a category entity into a flat array
 *
 * TODO (2014-07-25 11:30 by Gildas): Couldn't the Structured CategoryNormalizer class could be used here too?
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer extends BaseNormalizer
{
    /**  @var string[] */
    protected $supportedFormats = ['csv'];
}
