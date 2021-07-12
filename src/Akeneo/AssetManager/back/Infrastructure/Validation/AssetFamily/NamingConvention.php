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

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Symfony\Component\Validator\Constraint;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class NamingConvention extends Constraint
{
    private AssetFamilyIdentifier $assetFamilyIdentifier;
    private ?AttributeCode $attributeAsMainMedia;

    public function __construct(AssetFamilyIdentifier $assetFamilyIdentifier, ?AttributeCode $attributeAsMainMedia = null)
    {
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
        return NamingConventionValidator::class;
    }
}
