<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Infrastructure\Event;

// TODO move to domain
/**
 * Events dispached during installation process.
 */
final class InstallerEvents
{
    /**
     * This event is dispatched after having installed the database.
     *
     * You can use it to create new tables that are not managed with doctrine.
     */
    public const POST_DB_CREATE = 'pim_installer.post_db_create';

    /**
     * This event is dispatched before launching any assets dump command.
     */
    public const PRE_ASSETS_DUMP = 'pim_installer.pre_assets_dump';

    /**
     * This event is dispatched after launching all assets dump command.
     */
    public const POST_ASSETS_DUMP = 'pim_installer.post_assets_dump';

    /**
     * This event is dispatched after launching all assets dump command.
     */
    public const POST_SYMFONY_ASSETS_DUMP = 'pim_installer.post_symfony_assets_dump';

    /**
     * This event is dispatched before each fixture load.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance containing the job profile code.
     */
    public const PRE_LOAD_FIXTURE = 'pim_installer.pre_load_fixture';

    /**
     * This event is dispatched after each fixture load.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance containing the job profile code.
     */
    public const POST_LOAD_FIXTURE = 'pim_installer.post_load_fixture';

    /**
     * This event is dispatched before any fixture has been loaded.
     */
    public const PRE_LOAD_FIXTURES = 'pim_installer.pre_load_fixtures';

    /**
     * This event is dispatched after when all fixtures are loaded.
     */
    public const POST_LOAD_FIXTURES = 'pim_installer.post_load_fixtures';
}
