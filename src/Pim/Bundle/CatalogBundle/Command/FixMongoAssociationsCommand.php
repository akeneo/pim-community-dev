<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * In a Mongo Database, when you import associations through products, you can be in presence of these documents:
 *  {
 *      _id: ObjectId("58945ae949be10e4448b4568"),
 *      associations: [
 *          {
 *              _id: ObjectId("58945ae949be10e4448b4576"),
 *              products: [
 *                  {
 *                      $ref: 'pim_catalog_product',
 *                      $id: '58945ae949be10e4448b4568',
 *                      $db: ''
 *                  }
 *              ],
 *              ...
 *          }
 *      ],
 *      ...
 *  }
 *
 * This document is not correct because:
 * - The $id of the associated product is a string and should be an ObjectId
 * - the $db is empty
 *
 * This script allows to update all these documents by updating it to
 *  {
 *      _id: ObjectId("58945ae949be10e4448b4568"),
 *      associations: [
 *          {
 *              _id: ObjectId("58945ae949be10e4448b4576"),
 *              products: [
 *                  {
 *                      $ref: 'pim_catalog_product',
 *                      $id: ObjectId('58945ae949be10e4448b4568'),
 *                      $db: 'akeneo_pim'
 *                  }
 *              ],
 *              ...
 *          }
 *      ],
 *      ...
 *  }
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FixMongoAssociationsCommand extends ContainerAwareCommand
{
    /** @var string */
    protected $databaseName;

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        $storageDriver = $this->getContainer()->getParameter('pim_catalog_product_storage_driver');

        return AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM === $storageDriver;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:fix-mongo-associations')
            ->setDescription('Fix associated products for Mongo databases.');
    }

    /**
     * {@inheritdoc}
     *
     * @see https://docs.mongodb.com/v2.4/reference/operator/query/type/#op._S_type
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = $this->getDocumentManager()->getDocumentCollection($this->getClassName());

        $output->writeln('Looking for documents to fix...');

        $documentsToUpdate = $collection->find([
            'associations.products' => [
                '$elemMatch' => [
                    '$id' => [
                        // 2 === Type 'String', read the description for more information.
                        '$type' => 2
                    ]
                ]
            ]
        ]);

        $total = count($documentsToUpdate);
        $output->writeln(sprintf('%d document(s) found', $total));

        $count = 0;
        foreach ($documentsToUpdate as $document) {
            $query = [ '_id' => $document['_id'] ];
            $updateQuery = [
                '$set' => [
                    'associations' => $this->updateAssociations($document['associations'])
                ]
            ];
            $collection->update($query, $updateQuery, [ 'multiple' => false ]);

            $count ++;
            if (0 === $count % 100) {
                $output->write(sprintf('%d/%d ...', $count, $total));
            }
        }

        $output->write(sprintf('%s document(s) fixed with success.', $total));
    }

    /**
     * @param $associations
     * @return array
     */
    protected function updateAssociations($associations)
    {
        $newAssociations = [];
        foreach ($associations as $association) {
            $newAssociations[] = $this->updateAssociation($association);
        }

        return $newAssociations;
    }

    /**
     * @param $association
     * @return array
     */
    protected function updateAssociation($association)
    {
        return [
            '_id'             => $association['_id'],
            'associationType' => $association['associationType'],
            'owner'           => $association['owner'],
            'products'        => $this->updateProducts($association['products']),
            'groupIds'        => $association['groupIds'],
        ];
    }

    /**
     * @param $products
     * @return array
     */
    protected function updateProducts($products)
    {
        $newProducts = [];
        foreach ($products as $product) {
            $newProducts[] = $this->updateProduct($product);
        }

        return $newProducts;
    }

    /**
     * @param $product
     * @return array
     */
    protected function updateProduct($product)
    {
        return [
            '$ref' => $product['$ref'],
            '$id'  => new \MongoId($product['$id']),
            '$db'  => $this->getDatabaseName(),
        ];
    }

    /**
     * @return string
     */
    protected function getDatabaseName()
    {
        if (null !== $this->databaseName) {
            return $this->databaseName;
        }
        $this->databaseName = $this->getDocumentManager()->getDocumentDatabase($this->getClassName())->getName();

        return $this->databaseName;
    }

    /**
     * @return DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->getContainer()->get('pim_catalog.object_manager.product');
    }

    /**
     * @return string
     */
    protected function getClassName()
    {
        return $this->getContainer()->getParameter('pim_catalog.entity.product.class');
    }
}
