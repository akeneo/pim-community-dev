<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\ReorderTemplateAttributesCommand;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Query\UpdateCategoryTemplateAttributesOrder;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReorderTemplateAttributesCommandHandler
{
    public function __construct(
        private readonly GetAttribute $getAttribute,
        private readonly UpdateCategoryTemplateAttributesOrder $updateCategoryTemplateAttributesOrder,
    ) {
    }

    public function __invoke(ReorderTemplateAttributesCommand $command): void
    {
        $attributes = $this->getAttribute->byTemplateUuid(TemplateUuid::fromString($command->templateUuid));
        $attributes->reorder($command->attributeUuids);
        $this->updateCategoryTemplateAttributesOrder->fromAttributeCollection($attributes);
    }
}
