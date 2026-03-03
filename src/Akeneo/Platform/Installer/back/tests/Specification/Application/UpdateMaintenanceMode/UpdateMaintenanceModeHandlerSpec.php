<?php

declare(strict_types=1);

namespace Specification\Akeneo\Platform\Installer\Application\UpdateMaintenanceMode;

use Akeneo\Platform\Installer\Application\UpdateMaintenanceMode\UpdateMaintenanceModeCommand;
use Akeneo\Platform\Installer\Application\UpdateMaintenanceMode\UpdateMaintenanceModeHandler;
use Akeneo\Platform\Installer\Domain\Query\UpdateMaintenanceModeInterface;
use PhpSpec\ObjectBehavior;

class UpdateMaintenanceModeHandlerSpec extends ObjectBehavior
{
    public function let(
        UpdateMaintenanceModeInterface $updateMaintenanceMode,
    ): void {
        $this->beConstructedWith($updateMaintenanceMode);
    }

    public function it_is_instantiable(): void
    {
        $this->beAnInstanceOf(UpdateMaintenanceModeHandler::class);
    }

    public function it_updates_maintenance_mode(
        UpdateMaintenanceModeInterface $updateMaintenanceMode,
    ): void {
        $updateMaintenanceMode->execute(true)->shouldBeCalled();

        $this->handle(new UpdateMaintenanceModeCommand(true));
    }
}
