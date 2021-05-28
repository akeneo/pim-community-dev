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
 * Register the asset value validators by attribute type.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetValueValidatorRegistry
{
    /** @var AssetValueValidatorInterface[] */
    private array $validators = [];

    public function __construct(iterable $validators)
    {
        foreach ($validators as $validator) {
            $this->validators[$validator->forAttributeType()] = $validator;
        }
    }

    public function getValidator(string $attributeType): AssetValueValidatorInterface
    {
        if (!array_key_exists($attributeType, $this->validators)) {
            throw new \InvalidArgumentException(sprintf('There was no asset value validator found for the attribute type "%s"', $attributeType));
        }

        return $this->validators[$attributeType];
    }
}
