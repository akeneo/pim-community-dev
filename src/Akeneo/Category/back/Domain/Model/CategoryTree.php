<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Model;

use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

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
        private ?TemplateUuid $templateUuid = null,
        private ?LabelCollection $templateLabels = null,
    ) {
    }

    /**
     * @param array{
     *     id: int,
     *     code: string,
     *     translations: string|null,
     *     template_uuid: string|null,
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
        $templateUuid = $result['template_uuid'] ? TemplateUuid::fromString($result['template_uuid']) : null;
        $templateLabels = $result['template_labels'] ?
            LabelCollection::fromArray(
                json_decode($result['template_labels'], true, 512, JSON_THROW_ON_ERROR),
            ) : null;

        return new self($id, $code, $labelCollection, $templateUuid, $templateLabels);
    }

    public function getId(): ?CategoryId
    {
        return $this->id;
    }

    public function getCode(): Code
    {
        return $this->code;
    }

    public function getLabels(): ?LabelCollection
    {
        return $this->labels;
    }

    public function getTemplateUuid(): ?TemplateUuid
    {
        return $this->templateUuid;
    }

    public function getTemplateLabels(): ?LabelCollection
    {
        return $this->templateLabels;
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
     *     templateUuid: string|null,
     *     templateLabels: array<string, string>|null
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
            'templateUuid' => (string) $this->getTemplateUuid(),
            'templateLabels' => $this->getLabels()?->normalize(),
        ];
    }
}
