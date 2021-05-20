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
use Symfony\Component\Validator\Constraint;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class Transformation extends Constraint
{
    public const FILENAME_PREFIX_SUFFIX_EMPTY_ERROR = 'pim_asset_manager.asset_family.validation.transformation.filename_prefix_suffix_empty';
    public const EMPTY_OPERATION_LIST_ERROR = 'pim_asset_manager.asset_family.validation.transformation.empty_operation_list';

    private AssetFamilyIdentifier $assetFamilyIdentifier;

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
        return TransformationValidator::class;
    }
}
