<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeLimit
{
    private const LIMIT_LESS = null;

    /** @var ?string */
    private $limit;

    private function __construct(?string $limit)
    {
        Assert::nullOrStringNotEmpty($limit, 'The limit cannot be empty');
        if (self::LIMIT_LESS !== $limit) {
            Assert::numeric($limit);
        }
        $this->limit = $limit;
    }

    public static function fromString(string $minValue): self
    {
        return new self($minValue);
    }

    public static function limitLess(): self
    {
        return new self(self::LIMIT_LESS);
    }

    public function normalize(): ?string
    {
        return $this->limit;
    }

    public function isGreater(AttributeLimit $otherLimit)
    {
        $this->checkEitherLimitAreNotLimitless($otherLimit);

        return (float) $this->limit > (float) $otherLimit->limit;
    }

    public function isLower(AttributeLimit $otherLimit)
    {
        $this->checkEitherLimitAreNotLimitless($otherLimit);

        return (float) $this->limit < (float) $otherLimit->limit;
    }

    public function isLimitLess(): bool
    {
        return self::LIMIT_LESS === $this->limit;
    }

    private function checkEitherLimitAreNotLimitless(AttributeLimit $otherLimit): void
    {
        if ($otherLimit->isLimitLess() || $this->isLimitLess()) {
            throw new \LogicException('Impossible to compare limitless values');
        }
    }
}
