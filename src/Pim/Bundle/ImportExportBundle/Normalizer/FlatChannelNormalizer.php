<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

/**
 * A normalizer to transform a channel entity into a flat array
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatChannelNormalizer extends ChannelNormalizer
{
    /**
     * @var array
     */
    protected $supportedFormats = array('csv');
}
