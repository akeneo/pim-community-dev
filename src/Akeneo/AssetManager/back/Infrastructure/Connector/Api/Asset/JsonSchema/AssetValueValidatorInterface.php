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

/**
 * Validate the asset values of the same attribute type, using JSON Schema
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface AssetValueValidatorInterface
{
    /**
     * Returns the list of errors formatted as:
     * [
     *      'property' => 'description[0].data',
     *      'message'  => 'The property data is required'
     * ]
     *
     * Returns an empty array if there are no errors.
     */
    public function validate(array $normalizedAsset): array;

    public function forAttributeType(): string;
}
