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

namespace Akeneo\AssetManager\Infrastructure\Connector\Api\Asset\JsonSchema;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;

/**
 * Validate a asset using JSON Schema.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetValidator
{
    public function __construct(
        private AssetPropertiesValidator $assetPropertiesValidator,
        private AssetValuesValidator $assetValuesValidator
    ) {
    }

    /**
     * Returns the list of errors formatted as:
     * [
     *      'property' => 'labels.fr_FR',
     *      'message'  => 'NULL value found, but a string is required'
     * ]
     *
     * Returns an empty array if there are no errors.
     */
    public function validate(AssetFamilyIdentifier $assetFamilyIdentifier, array $normalizedAsset): array
    {
        $errors = $this->assetPropertiesValidator->validate($normalizedAsset);

        if (empty($errors) && !empty($normalizedAsset['values'])) {
            $errors = array_merge($errors, $this->assetValuesValidator->validate($assetFamilyIdentifier, $normalizedAsset));
        }

        return $errors;
    }
}
