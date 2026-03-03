<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Model\Classification;

use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTree
{
    public function __construct(
        private ?CategoryId $id,
        private Code $code,
        private ?LabelCollection $labels = null,
        private ?CategoryTreeTemplate $categoryTreeTemplate = null,
    ) {
    }

    /**
     * @param array{
     *     id: int,
     *     code: string,
     *     translations: string|null,
     *     template_uuid: string|null,
     *     template_code: string|null,
     *     template_labels: string|null
     * } $result
     */
    public static function fromDatabase(array $result): self
    {
        $id = new CategoryId((int) $result['id']);
        $code = new Code($result['code']);
        $labelCollection = $result['translations'] ?
            LabelCollection::fromArray(
                json_decode($result['translations'], true, 512, JSON_THROW_ON_ERROR),
            ) : null;

        $categoryTreeTemplate = $result['template_uuid'] && $result['template_code'] && $result['template_labels']
                ? CategoryTreeTemplate::fromDatabase($result)
                : null
        ;

        return new self($id, $code, $labelCollection, $categoryTreeTemplate);
    }

    public function getId(): ?CategoryId
    {
        return $this->id;
    }

    public function getCode(): Code
    {
        return $this->code;
    }

    public function getLabel(string $localeCode): string
    {
        $label = $this->labels?->getTranslation($localeCode);

        if (!$label) {
            return '['.$this->code.']';
        }

        return $label;
    }

    public function getLabels(): ?LabelCollection
    {
        return $this->labels;
    }

    public function getCategoryTreeTemplate(): ?CategoryTreeTemplate
    {
        return $this->categoryTreeTemplate;
    }

    public function setLabel(string $localeCode, string $label): void
    {
        $this->labels->setTranslation($localeCode, $label);
    }

    /**
     * @return array{
     *     id: int|null,
     *     properties: array{
     *       code: string,
     *       labels: array<string, string>|null
     *     },
     *     categoryTreeTemplate: array{
     *       templateUuid: string|null,
     *       templateLabels: array<string, string>|null
     *     }|null
     * }
     */
    public function normalize(): array
    {
        return [
            'id' => $this->getId()?->getValue(),
            'properties' => [
                'code' => (string) $this->getCode(),
                'labels' => $this->getLabels()?->normalize(),
            ],
            'categoryTreeTemplate' => $this->getCategoryTreeTemplate()?->normalize(),
        ];
    }
}
