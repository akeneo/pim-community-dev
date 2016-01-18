<?php

namespace Pim\Bundle\TransformBundle\Normalizer\Flat;

use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Pim\Bundle\TransformBundle\Normalizer\Structured;

/**
 * Flat family normalizer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer extends Structured\FamilyNormalizer
{
    /** @var string[] */
    protected $supportedFormats = array('csv');

    /**
     * {@inheritdoc}
     */
    protected function normalizeAttributes(FamilyInterface $family, array $context = [])
    {
        $attributes = parent::normalizeAttributes($family);

        return implode(',', $attributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeRequirements(FamilyInterface $family)
    {
        $requirements = parent::normalizeRequirements($family);
        $flat = array();
        foreach ($requirements as $channel => $attributes) {
            $flat[] = $channel.':'.implode(',', $attributes);
        }

        return implode('|', $flat);
    }
}
