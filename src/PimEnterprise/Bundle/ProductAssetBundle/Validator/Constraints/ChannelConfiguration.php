<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for channel configuration
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ChannelConfiguration extends Constraint
{
    /** @var string */
    public $unknownTransformation = 'Transformation "%transformation%" is unknown';

    /** @var string */
    public $invalidConfiguration = 'Transformation "%transformation%" is not well configured (%error%)';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pimee_product_asset_channel_configuration_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
