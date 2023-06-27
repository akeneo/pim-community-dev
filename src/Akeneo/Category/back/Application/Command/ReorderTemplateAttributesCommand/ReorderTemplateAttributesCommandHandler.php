<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\ReorderTemplateAttributesCommand;

use Akeneo\Category\Domain\Query\UpdateCategoryTemplateAttributesOrder;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReorderTemplateAttributesCommandHandler
{
    public function __construct(
        private readonly UpdateCategoryTemplateAttributesOrder $updateCategoryTemplateAttributesOrder,
    ) {
    }

    public function __invoke(ReorderTemplateAttributesCommand $command): void
    {
        $this->updateCategoryTemplateAttributesOrder->fromAttributeUuids($command->attributeUuids);
    }
}
