<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Akeneo\Category\Application\Query\MarkTemplateAsDeactivated;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivateTemplateCommandHandler
{
    public function __construct(
        private readonly MarkTemplateAsDeactivated $markTemplateAsDeactivated
    ) {
    }

    public function __invoke(DeactivateTemplateCommand $command): void
    {
        $templateUuid = $command->uuid();
        $this->markTemplateAsDeactivated->execute($templateUuid);
    }
}
