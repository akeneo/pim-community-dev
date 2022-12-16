<?php

declare(strict_types=1);

namespace Akeneo\Category\ServiceApi;

use Akeneo\Category\Domain\ValueObject\ValueCollection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type NormalizedValue from ValueCollection
 */
class ExternalApiCategory
{
    /**
     * @param array<string, string>|null $labels
     * @param array<string, NormalizedValue> $values
     */
    public function __construct(
        private readonly string $code,
        private readonly ?array $values,
        private readonly string $updated,
        private readonly ?int $parentId = null,
        private readonly ?string $parentCode = null,
        private readonly ?array $labels = null,
        private ?int $position = null, // TODO: Handle position GRF-633
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getParentCode(): ?string
    {
        return $this->parentCode;
    }

    public function getUpdated(): ?string
    {
        return $this->updated;
    }

    /** @return array<string, string>|null */
    public function getLabels(): ?array
    {
        return $this->labels;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /** @return array<string, NormalizedValue> $values */
    public function getValues(): ?array
    {
        return $this->values;
    }

    /**
     * @param array{
     *     id: string,
     *     code: string,
     *     parent_id: string|null,
     *     parent_code: string|null,
     *     root_id: string|null,
     *     updated: string,
     *     lft: string|null,
     *     rgt: string|null,
     *     lvl: string|null,
     *     translations: string|null,
     *     value_collection: string|null,
     * } $category
     */
    public static function fromDatabase(array $category): self
    {
        self::assertArrayFromDatabase($category);

        $updatedDate = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $category['updated'])->format('c');
        $translations = $category['translations'] ? json_decode($category['translations'], true, 512, JSON_THROW_ON_ERROR) : [];

        $valueCollection = $category['value_collection'] ? json_decode($category['value_collection'], true, 512, JSON_THROW_ON_ERROR) : null;
        if (null !== $valueCollection) {
            $valueCollection = ValueCollection::fromDatabase($valueCollection)->normalize();
        }

        // TODO: Handle position GRF-633
        return new self(
            code: $category['code'],
            values: $valueCollection,
            updated: $updatedDate,
            parentId: isset($category['parent_id']) ? (int) $category['parent_id'] : null,
            parentCode: $category['parent_code'],
            labels: $translations,
            position: null,
        );
    }

    /**
     * @return array{
     *     code: string,
     *     parent: string|null,
     *     updated: string,
     *     labels: array<string, string>|null,
     *     position: int|null,
     *     values: array<string, array<string, mixed>>|null
     * }
     */
    public function normalize(bool $withPosition, bool $withEnrichedAttributes): array
    {
        $normalizedCategory = [
            'code' => $this->getCode(),
            'parent' => $this->getParentCode(),
            'updated' => $this->getUpdated(),
            'labels' => $this->getLabels(),
        ];

        if ($withPosition) {
            $normalizedCategory['position'] = $this->getPosition();
        }

        if ($withEnrichedAttributes) {
            $normalizedCategory['values'] = $this->getValues();
        }

        return $normalizedCategory;
    }

    /**
     * @param array{
     *     id: string,
     *     code: string,
     *     parent_id: string|null,
     *     parent_code: string|null,
     *     root_id: string|null,
     *     updated: string,
     *     lft: string|null,
     *     rgt: string|null,
     *     lvl: string|null,
     *     translations: string|null,
     *     value_collection: string|null,
     * } $category
     */
    private static function assertArrayFromDatabase(array $category): void
    {
        Assert::keyExists($category, 'id');
        Assert::nullOrString($category['id']);
        Assert::keyExists($category, 'code');
        Assert::string($category['code'], 'code');
        Assert::keyExists($category, 'parent_id');
        Assert::nullOrString($category['parent_id']);
        Assert::keyExists($category, 'parent_code');
        Assert::nullOrString($category['parent_code']);
        Assert::keyExists($category, 'root_id');
        Assert::nullOrString($category['root_id']);
        Assert::keyExists($category, 'updated');
        Assert::string($category['updated']);
        Assert::keyExists($category, 'lft');
        Assert::nullOrString($category['lft']);
        Assert::keyExists($category, 'rgt');
        Assert::nullOrString($category['rgt']);
        Assert::keyExists($category, 'lvl');
        Assert::nullOrString($category['lvl']);
        Assert::keyExists($category, 'translations');
        Assert::nullOrString($category['translations']);
        Assert::keyExists($category, 'value_collection');
        Assert::nullOrString($category['value_collection']);
    }
}
