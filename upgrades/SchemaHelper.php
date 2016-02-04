<?php

namespace Pim\Upgrade;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Schema helper
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SchemaHelper
{
    /** @var array */
    protected $classMapping = [
        'product'                 => 'pim_catalog.entity.product.class',
        'product_value'           => 'pim_catalog.entity.product_value.class',
        'product_media'           => 'pim_catalog.entity.product_media.class',
        'product_template'        => 'pim_catalog.entity.product_template.class',
        'attribute'               => 'pim_catalog.entity.attribute.class',
        'attribute_option'        => 'pim_catalog.entity.attribute_option.class',
        'group'                   => 'pim_catalog.entity.group.class',
        'version'                 => 'pim_versioning.entity.version.class',
        'product_draft'           => 'pimee_workflow.model.product_draft.class',
        'published_product'       => 'pimee_workflow.entity.published_product.class',
        'published_product_media' => 'pimee_workflow.entity.published_product_media.class',
    ];

    /** @var array resources linked to product (ie that are stored in same storage that the products) */
    protected $productResources = [
        'product',
        'product_value',
        'product_media',
        'version',
        'published_product',
        'published_product_media',
        'product_draft'
    ];

    /** @var ContainerInterface */
    protected $container;

    /** @var UpgradeHelper */
    protected $upgradeHelper;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->upgradeHelper = new UpgradeHelper($container);
    }

    /**
     * Get the table or collection name of a resource by looking at the class metadata of the entities and documents.
     *
     * @param string $resource
     *
     * @return string
     */
    public function getTableOrCollection($resource)
    {
        if (!array_key_exists($resource, $this->classMapping)) {
            $error = 'Can not get the table for the object "%s". Only the following types %s are known.';
            throw new \InvalidArgumentException(sprintf($error,
                $resource, implode(', ', array_keys($this->classMapping))));
        }

        if ($this->upgradeHelper->areProductsStoredInMongo() && in_array($resource, $this->productResources)) {
            return $this->getTableOrCollectionForMongo($resource);
        }

        return $this->getTableOrCollectionForOrm($resource);
    }

    /**
     * @param string $resource
     *
     * @return string
     */
    private function getTableOrCollectionForMongo($resource)
    {
        $class = $this->container->getParameter($this->classMapping[$resource]);

        return $this->getDocumentManager()->getClassMetadata($class)->getCollection();
    }

    /**
     * @param string $resource
     *
     * @return string
     */
    private function getTableOrCollectionForOrm($resource)
    {
        $class = $this->container->getParameter($this->classMapping[$resource]);

        return $this->getEntityManager()->getClassMetadata($class)->getTableName();
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * @return DocumentManager
     */
    private function getDocumentManager()
    {
        return $this->container->get('doctrine.odm.mongodb.document_manager');
    }
}
