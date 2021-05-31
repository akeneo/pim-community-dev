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

namespace Akeneo\AssetManager\Domain\Model\Asset\Value;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Webmozart\Assert\Assert;

/**
 * Collection of options stored in a asset value, for "option collection" attributes.
 *
 * @see \Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class OptionCollectionData implements ValueDataInterface
{
    /** @var string[] */
    private array $optionCodes;

    private function __construct(array $optionCodes)
    {
        Assert::notEmpty($optionCodes, 'Option codes should be a non empty array');

        $this->optionCodes = $optionCodes;
    }

    public function equals(ValueDataInterface $valueData): bool
    {
        return $valueData instanceof self && $valueData->normalize() === $this->normalize();
    }

    /**
     * @return string[]
     */
    public function normalize(): array
    {
        return $this->optionCodes;
    }

    public static function createFromNormalize($normalizedData): ValueDataInterface
    {
        Assert::isArray($normalizedData, 'Normalized data should be an array');
        Assert::allString($normalizedData, 'Normalized data should be an array of string');

        return new self($normalizedData);
    }

    public static function fromOptionCodes(array $optionCodes): OptionCollectionData
    {
        Assert::allIsInstanceOf(
            $optionCodes,
            OptionCode::class,
            sprintf('Each option code should be an instance of "%s"', OptionCode::class)
        );

        $optionCodesString = array_map('strval', $optionCodes);

        return new self($optionCodesString);
    }
}
