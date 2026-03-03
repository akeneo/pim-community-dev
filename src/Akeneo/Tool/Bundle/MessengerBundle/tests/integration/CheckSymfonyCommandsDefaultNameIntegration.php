<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\tests\integration;

use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CheckSymfonyCommandsDefaultNameIntegration extends KernelTestCase
{
    /**
     * Defining the default name of a Symfony command defined as service, makes its lazy loaded.
     * It's the recommended way to register Symfony console commands,
     *  and we want them all to be lazy loaded to keep good performances when consuming messages with the use of console command for UCS.
     * So this test will prevent a new Akeneo console command without default name from being added.
     */
    public function test_all_symfony_commands_have_a_default_name(): void
    {
        $container = static::getContainer();

        // The parameter 'console.command.ids' contains the commands with deprecated registration (i.e. without defined static $defaultName)
        $commandsWithoutDefaultName = \array_filter(
            $container->getParameter('console.command.ids'),
            fn (string $commandId) => \str_starts_with($commandId, 'Akeneo')
        );

        Assert::assertEmpty(
            $commandsWithoutDefaultName,
            sprintf("Every following command must have a static default name:\n%s", implode("\n", $commandsWithoutDefaultName))
        );
    }
}
