<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\Group;

/**
 * A normalizer to transform a group entity into a flat array
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatGroupNormalizer extends GroupNormalizer
{
    /**
     * @var array
     */
    protected $supportedFormats = array('csv');

    /**
     * {@inheritdoc}
     */
    protected function normalizeAttributes(Group $group)
    {
        $attributes = parent::normalizeAttributes($group);

        return implode(',', $attributes);
    }
}
