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

use JsonSchema\Validator;

/**
 * Validate the structure of a assets list (but not the assets themselves).
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetListValidator
{
    public function validate(array $normalizedAssetList): array
    {
        $validator = new Validator();
        $normalizedAssetListObject = json_decode(json_encode($normalizedAssetList));

        $validator->validate($normalizedAssetListObject, $this->getJsonSchema());

        return $validator->getErrors();
    }

    private function getJsonSchema(): array
    {
        return [
            'type' => 'array',
            'items' => [
                'type' => 'object'
            ]
        ];
    }
}
