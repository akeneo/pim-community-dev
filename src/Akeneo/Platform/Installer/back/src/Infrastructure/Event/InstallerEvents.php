<?php

namespace Akeneo\Platform\Installer\Infrastructure\Event;

/**
 * Events dispached during installation process.
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

    /**
     * This event is dispatched after the instance has been reset.
     */
    public const POST_RESET_INSTANCE = 'pim_installer.post_reset_instance';
}
