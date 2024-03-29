<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\ReorderTemplateAttributesCommand;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Query\GetTemplate;
use Akeneo\Category\Domain\Query\UpdateCategoryTemplateAttributesOrder;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReorderTemplateAttributesCommandHandler
{
    public function __construct(
        private readonly GetTemplate $getTemplate,
        private readonly GetAttribute $getAttribute,
        private readonly UpdateCategoryTemplateAttributesOrder $updateCategoryTemplateAttributesOrder,
    ) {
    }

    public function __invoke(ReorderTemplateAttributesCommand $command): void
    {
        $template = $this->getTemplate->byUuid(TemplateUuid::fromString($command->templateUuid));

        $attributes = $this->getAttribute->byTemplateUuid($template->getUuid());
        $attributes->reorder($command->attributeUuids);
        $this->updateCategoryTemplateAttributesOrder->fromAttributeCollection($attributes);
    }
}
