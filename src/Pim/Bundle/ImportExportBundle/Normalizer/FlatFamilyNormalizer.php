<?php

namespace Pim\Bundle\ImportExportBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Flat family normalizer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlatFamilyNormalizer extends FamilyNormalizer
{
    /**
     * @var array
     */
    protected $supportedFormats = ['csv'];

    /**
     * {@inheritdoc}
     */
    protected function normalizeAttributes(Family $family)
    {
        $attributes = parent::normalizeAttributes($family);

        return implode(',', $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeRequirements(Family $family)
    {
        $requirements = parent::normalizeRequirements($family);
        $flat = [];
        foreach ($requirements as $channel => $attributes) {
            $flat[] = $channel.':'.implode(',', $attributes);
        }

        return implode('|', $flat);
    }
}
