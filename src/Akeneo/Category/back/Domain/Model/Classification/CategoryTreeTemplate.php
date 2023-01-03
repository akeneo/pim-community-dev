<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Model\Classification;

use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryTreeTemplate
{
    public function __construct(
        private ?TemplateUuid $templateUuid = null,
        private ?TemplateCode $templateCode = null,
        private ?LabelCollection $templateLabels = null,
    ) {
    }

    /**
     * @param array{
     *     template_uuid: string,
     *     template_code: string,
     *     template_labels: string|null
     * } $result
     */
    public static function fromDatabase(array $result): self
    {
        $templateUuid = $result['template_uuid'] ? TemplateUuid::fromString($result['template_uuid']) : null;
        $templateCode = $result['template_code'] ? TemplateCode::fromString($result['template_code']) : null;
        $templateLabels = $result['template_labels'] ?
            LabelCollection::fromArray(
                json_decode($result['template_labels'], true, 512, JSON_THROW_ON_ERROR),
            ) : null;

        return new self($templateUuid, $templateCode, $templateLabels);
    }

    public function getTemplateUuid(): TemplateUuid
    {
        return $this->templateUuid;
    }

    public function getTemplateCode(): TemplateCode
    {
        return $this->templateCode;
    }

    public function getTemplateLabels(): ?LabelCollection
    {
        return $this->templateLabels;
    }

    public function getTemplateLabel(string $localeCode): ?string
    {
        $label = $this->templateLabels?->getTranslation($localeCode);

        if (!$label) {
            return null;
        }

        return $label;
    }

    /**
     * @return array{
     *     templateUuid: string|null,
     *     templateLabels: array<string, string>|null
     * }
     */
    public function normalize(): array
    {
        return [
            'templateUuid' => (string) $this->getTemplateUuid(),
            'templateLabels' => $this->getTemplateLabels()?->normalize(),
            'templateCode' => (string) $this->getTemplateCode(),
        ];
    }
}
