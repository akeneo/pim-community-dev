<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Akeneo\Category\Application\Query\DeactivateTemplate;
use Akeneo\Category\Domain\Event\TemplateDeactivatedEvent;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivateTemplateCommandHandler
{
    public function __construct(
        private readonly DeactivateTemplate $deactivateTemplate,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(DeactivateTemplateCommand $command): void
    {
        $templateUuid = TemplateUuid::fromString($command->uuid());
        $this->deactivateTemplate->execute($templateUuid);
        $this->eventDispatcher->dispatch(new TemplateDeactivatedEvent($templateUuid));
    }
}
