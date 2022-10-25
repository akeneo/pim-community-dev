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
            'templateLabels' => (string) $this->getLabels()?->normalize(),
        ];
    }
}
