<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if the asset family is well configured for attribute entity.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class IsAssetFamilyConfigured extends Constraint
{
    /** @var string */
    public $unknownMessage = 'The asset family "%asset_family_identifier%" does not exist.';

    /** @var string */
    public $invalidMessage = 'The asset family "%asset_family_identifier%" identifier is not valid';

    /** @var string */
    public $emptyMessage = 'You need to define an asset family type for your attribute';

    /** @var string */
    public $propertyPath = 'reference_data_name';

    /**
     * {@inheritdoc}
     */
    public function validatedBy(): string
    {
        return 'pim_is_asset_family_configured_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
