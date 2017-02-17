<?php

namespace Pim\Bundle\InstallerBundle\Event;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
final class InstallerEvents
{
    /**
     * This event is dispatched after having install the databases (ORM + MongoDB)
     *
     * You can use it to create new tables that are not managed with doctrine.
     *
     * @const string
     */
    const POST_DB_CREATE = 'pim_installer.post_db_create';

    /**
     * This event is dispatched before launching any assets dump command
     *
     * @const string
     */
    const PRE_ASSETS_DUMP = 'pim_installer.pre_assets_dump';

    /**
     * This event is dispatched after launching all assets dump command
     *
     * @const string
     */
    const POST_ASSETS_DUMP = 'pim_installer.post_assets_dump';

    /**
     * This event is dispatched before each fixture load.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance with the job profile code.
     *
     * @const string
     */
    const PRE_LOAD_FIXTURE = 'pim_installer.pre_load_fixture';

    /**
     * This event is dispatched after each fixture load.
     *
     * The event listener receives an
     * Symfony\Component\EventDispatcher\GenericEvent instance with the job profile code.
     *
     * @const string
     */
    const POST_LOAD_FIXTURE = 'pim_installer.post_load_fixture';

    /**
     * This event is dispatched before any fixture has been loaded.
     *
     * @const string
     */
    const PRE_LOAD_FIXTURES = 'pim_installer.pre_load_fixtures';

    /**
     * This event is dispatched after when all fixtures are loaded.
     *
     * @const string
     */
    const POST_LOAD_FIXTURES = 'pim_installer.post_load_fixtures';
}
