<?php

declare(strict_types=1);

/*
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Bundle\Event;

use Webmozart\Assert\Assert;

final class AttributeOptionWasCreated
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $code,
        public readonly ?string $attributeCode,
        public readonly ?\DateTimeImmutable $createdAt,
    ) {
    }

    public function normalize(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'attribute_code' => $this->attributeCode,
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }

    public static function denormalize(array $normalized): self
    {
        Assert::keyExists($normalized, 'id');
        Assert::integer($normalized['id']);
        Assert::keyExists($normalized, 'code');
        Assert::string($normalized['code']);
        Assert::keyExists($normalized, 'attribute_code');
        Assert::string($normalized['attribute_code']);
        Assert::keyExists($normalized, 'created_at');
        Assert::string($normalized['created_at']);
        $date = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $normalized['created_at']);
        Assert::isInstanceOf($date, \DateTimeImmutable::class, \sprintf('Date is not well formatted: %s', $normalized['created_at']));

        return new AttributeOptionWasCreated(
            $normalized['id'],
            $normalized['code'],
            $normalized['attribute_code'],
            $date
        );
    }
}
