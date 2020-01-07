<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\NamingConvention;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Symfony\Component\Validator\Constraint;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RawSource extends Constraint
{
    public const INVALID_PROPERTY_ERROR = 'pim_asset_manager.asset_family.validation.naming_convention.invalid_property';
    public const NO_ATTRIBUTE_AS_MAIN_MEDIA = 'pim_asset_manager.asset_family.validation.naming_convention.no_attribute_as_main_media';
    public const NO_MEDIA_LINK_AS_MAIN_MEDIA = 'pim_asset_manager.asset_family.validation.naming_convention.no_media_link_as_main_media';

    /** @var AssetFamilyIdentifier */
    private $assetFamilyIdentifier;

    public function __construct(AssetFamilyIdentifier $assetFamilyIdentifier)
    {
        parent::__construct();

        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
    }

    public function getAssetFamilyIdentifier(): AssetFamilyIdentifier
    {
        return $this->assetFamilyIdentifier;
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return RawSourceValidator::class;
    }
}
