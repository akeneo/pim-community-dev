<?php

class Helper
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;

    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getDbConnection()
    {
        return $this->getEm()->getConnection();
    }

    public function truncateTable($table)
    {
        $connection = $this->getDbConnection();
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
        $connection->executeUpdate($connection->getDatabasePlatform()->getTruncateTableSQL($table));
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\LocaleRepository
     */
    public function getLocaleRepository()
    {
        return $this->container->get('pim_catalog.repository.locale');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository\ChannelRepository
     */
    public function getChannelRepository()
    {
        return $this->container->get('pim_catalog.repository.channel');
    }

    /**
     * @return \Akeneo\Component\FileStorage\RawFile\RawFileStorer
     */
    public function getRawFileStorer()
    {
        return $this->container->get('akeneo_file_storage.file_storage.raw_file.storer');
    }

    /**
     * Delete all files from storage
     */
    public function cleanFilesystem()
    {
        $mountManager = $this->container->get('oneup_flysystem.mount_manager');
        $fs = $mountManager->getFilesystem(\PimEnterprise\Component\ProductAsset\FileStorage::ASSET_STORAGE_ALIAS);
        foreach ($fs->listContents() as $directory) {
            $fs->deleteDir($directory['path']);
        }
    }
}
