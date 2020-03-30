<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * This class represents an operand used in a calculate action operation. It can either be:
 *  - an attribute value (attribute code, channel code and locale code)
 *  - a plain constant numeric value
 */
final class Operand
{
    /** @var string|null */
    private $attributeCode;

    /** @var string|null */
    private $channelCode;

    /** @var string|null */
    private $localeCode;

    /** @var float|null */
    private $constantValue;

    private function __construct(?string $attributeCode, ?string $channelCode, ?string $localeCode, ?float $constantValue)
    {
        $this->attributeCode = $attributeCode;
        $this->channelCode = $channelCode;
        $this->localeCode = $localeCode;
        $this->constantValue = $constantValue;
    }

    public static function fromNormalized(array $data): self
    {
        if (array_key_exists('field', $data)) {
            return self::fromAttributeValue($data);
        } elseif (array_key_exists('value', $data)) {
            return self::fromConstant($data);
        }
        throw new \InvalidArgumentException('An operation expects one of the "field" or "value" keys');
    }

    private static function fromAttributeValue(array $data): self
    {
        Assert::keyNotExists($data, 'value', 'An operation cannot be defined with both the "field" and "value" keys');

        Assert::string($data['field']);
        $channelCode = $data['scope'] ?? null;
        Assert::nullOrString($channelCode);
        $localeCode = $data['locale'] ?? null;
        Assert::nullOrString($localeCode);

        return new self($data['field'], $channelCode, $localeCode, null);
    }

    private static function fromConstant(array $data): self
    {
        Assert::keyNotExists($data, 'field');
        Assert::keyNotExists($data, 'scope');
        Assert::keyNotExists($data, 'locale');

        Assert::numeric($data['value'], 'Operand expects a numeric "value" key');

        return new self(null, null, null, (float) $data['value']);
    }

    public function getAttributeCode(): ?string
    {
        return $this->attributeCode;
    }

    public function getChannelCode(): ?string
    {
        return $this->channelCode;
    }

    public function getLocaleCode(): ?string
    {
        return $this->localeCode;
    }

    public function getConstantValue(): ?float
    {
        return $this->constantValue;
    }
}
