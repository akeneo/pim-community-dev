<?php

namespace Akeneo\Pim\Enrichment\Bundle\StructureVersion\EventListener;

use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener on the install command to create the structure version table
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TableCreator implements EventSubscriberInterface
{
    /** @var RegistryInterface */
    protected $doctrine;

    /**
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'onPostDBCreate'
        ];
    }

    /**
     * Add the csv format
     */
    public function onPostDBCreate()
    {
        $sql = <<<'SQL'
DROP TABLE IF EXISTS akeneo_structure_version_last_update;
CREATE TABLE akeneo_structure_version_last_update (
    resource_name varchar(255) NOT NULL,
    last_update datetime NOT NULL COMMENT '(DC2Type:datetime)',
    PRIMARY KEY(resource_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $this->doctrine->getConnection()->exec($sql);
    }
}
