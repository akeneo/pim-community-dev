<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Event;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeGroupActivationHasChanged
{
    public function __construct(
        public readonly string $attributeGroupCode,
        public readonly bool $newIsActivated,
        public readonly \DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(): array
    {
        return [
            'attribute_group_code' => $this->attributeGroupCode,
            'new_is_activated' => $this->newIsActivated,
            'updated_at' => $this->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function denormalize(array $normalized): AttributeGroupActivationHasChanged
    {
        Assert::isArray($normalized);
        Assert::keyExists($normalized, 'attribute_group_code');
        Assert::stringNotEmpty($normalized['attribute_group_code']);

        Assert::keyExists($normalized, 'new_is_activated');
        Assert::boolean($normalized['new_is_activated']);

        Assert::keyExists($normalized, 'updated_at');
        Assert::stringNotEmpty($normalized['updated_at']);
        $date = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $normalized['updated_at']);
        Assert::isInstanceOf($date, \DateTimeImmutable::class, \sprintf('Date is not well formatted: %s given', $normalized['updated_at']));

        return new AttributeGroupActivationHasChanged(
            $normalized['attribute_group_code'],
            $normalized['new_is_activated'],
            $date
        );
    }
}
