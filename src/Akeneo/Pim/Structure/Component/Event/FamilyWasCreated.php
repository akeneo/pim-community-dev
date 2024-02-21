<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Event;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FamilyWasCreated implements FamilyWasCreatedOrUpdated
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly \DateTimeImmutable $createdAt
    ) {
        Assert::stringNotEmpty($code);
    }

    public function normalize(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'created_at' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }

    public static function denormalize(array $normalized): FamilyWasCreated
    {
        Assert::keyExists($normalized, 'id');
        Assert::integer($normalized['id']);

        Assert::keyExists($normalized, 'code');
        Assert::string($normalized['code']);

        Assert::keyExists($normalized, 'created_at');
        Assert::string($normalized['created_at']);
        $date = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $normalized['created_at']);
        Assert::isInstanceOf($date, \DateTimeImmutable::class, \sprintf('Date is not well formatted: %s given', $normalized['created_at']));

        return new FamilyWasCreated($normalized['id'], $normalized['code'], $date);
    }
}
