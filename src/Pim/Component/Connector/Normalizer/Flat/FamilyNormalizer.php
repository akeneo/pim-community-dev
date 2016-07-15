<?php

namespace Pim\Component\Connector\Normalizer\Flat;

use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Normalizer\Structured\FamilyNormalizer as BaseNormalizer;

/**
 * Flat family normalizer
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyNormalizer extends BaseNormalizer
{
    /** @var string[] */
    protected $supportedFormats = ['csv'];

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
        $flat = [];
        foreach ($requirements as $channel) {
            foreach ($channel as $key => $attributes) {
                $flat['requirements-' . $key] = implode(',', $attributes);
            }
        }

        return $flat;
    }
}
