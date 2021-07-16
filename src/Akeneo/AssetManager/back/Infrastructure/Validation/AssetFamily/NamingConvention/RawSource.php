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
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Symfony\Component\Validator\Constraint;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class RawSource extends Constraint
{
    public const ATTRIBUTE_NOT_FOUND_ERROR = 'pim_asset_manager.asset_family.validation.naming_convention.property_should_exist';
    public const ATTRIBUTE_IS_NOT_MAIN_MEDIA = 'pim_asset_manager.asset_family.validation.naming_convention.not_main_media_attribute_code';

    private AssetFamilyIdentifier $assetFamilyIdentifier;
    private ?AttributeCode $attributeAsMainMedia;

    public function __construct(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        ?AttributeCode $attributeAsMainMedia
    ) {
        parent::__construct();

        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->attributeAsMainMedia = $attributeAsMainMedia;
    }

    public function getAssetFamilyIdentifier(): AssetFamilyIdentifier
    {
        return $this->assetFamilyIdentifier;
    }

    public function getAttributeAsMainMedia(): ?AttributeCode
    {
        return $this->attributeAsMainMedia;
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
