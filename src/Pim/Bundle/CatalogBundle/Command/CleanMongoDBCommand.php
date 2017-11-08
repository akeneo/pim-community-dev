<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command that removes obsolete relations and migrates normalizedData for MongoDB documents.
 *
 * @author    Remy Betus <remy.betus@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 30)
 */
class CleanMongoDBCommand extends ContainerAwareCommand
{
    const MONGODB_PRODUCT_COLLECTION = 'pim_catalog_product';

    /** @var array $familyIds */
    protected $familyIds;

    /** @var array $associationTypeIds */
    protected $associationTypeIds;

    /** @var array $categoryIds */
    protected $categoryIds;

    /** @var array $attributes */
    protected $attributes;

    /** @var array $optionIds */
    protected $optionIds;

    /** @var array $missingEntities */
    protected $missingEntities;

    /** @var array */
    protected $referenceDataFields;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:mongodb:clean')
            ->setDescription(
                'Cleans MongoDB documents: removes missing related entities and then fix normalizedData'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                "Do the checks, display errors but do not update any products."
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->missingEntities = [];
        $storageDriver = $this->getContainer()->getParameter('pim_catalog_product_storage_driver');

        if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM !== $storageDriver) {
            $output->writeln('<error>This command could be only launched on MongoDB storage</error>');

            return -1;
        }

        $this->findMissingEntities($output, $input->getOption('dry-run'));

        $this->printReport($output);

        return 0;
    }

    /**
     * Displays report
     *
     * @param OutputInterface $output
     */
    protected function printReport(OutputInterface $output)
    {
        $table = $this->getHelper('table');
        $table->setHeaders(['Entity class', 'ID', '# missing']);

        $table->setRows([]);

        foreach ($this->missingEntities as $missingEntityName => $missingEntity) {
            foreach ($missingEntity as $id => $count) {
                $table->addRow([$missingEntityName, $id, $count]);
            }
        }

        $table->render($output);
    }

    /**
     * Finds missing entities (family, channel, attribute, option(s), reference data). A dry run option is available
     * so it won't update anything.
     *
     * @param OutputInterface $output
     * @param bool            $dryRun
     */
    protected function findMissingEntities(OutputInterface $output, $dryRun = false)
    {
        $db = $this->getMongoConnection();
        $productCollection = new \MongoCollection($db, self::MONGODB_PRODUCT_COLLECTION);

        $products = $productCollection->find([]);
        $numberOfProducts = $products->count();
        $output->writeln(
            sprintf(
                '%sCleaning MongoDB documents for <comment>%s</comment> (<comment>%s</comment> entries).',
                ($dryRun) ? 'DRY RUN (check only) - ' : '',
                self::MONGODB_PRODUCT_COLLECTION,
                number_format($numberOfProducts, 0, '.', ',')
            )
        );

        $processedProducts = 0;
        $startTime = new \DateTime('now');
        foreach ($products as $product) {
            $product = $this->checkFamily($product);
            $product = $this->checkCategories($product);
            $product = $this->checkValues($product);
            $product = $this->checkAssociations($product);

            if (!$dryRun) {
                $values = [];
                foreach ($product['values'] as $value) {
                    $values[] = $value;
                }
                $product['values'] = $values;

                $productCollection->update(
                    ['_id' => new \MongoId($product['_id'])],
                    $product
                );
            }

            if (0 === $processedProducts % 1500) {
                $this->displayProgress($output, $startTime, $numberOfProducts, $processedProducts);
            }
            $processedProducts++;
        }
        $output->writeln('<comment>finished!</comment>');
    }

    /**
     * Custom progress display. Progress component from Console was slowing down the
     * process too much.
     *
     * @param OutputInterface $output
     * @param \DateTime       $startTime
     * @param integer         $numberOfProducts
     * @param integer         $processedProducts
     */
    protected function displayProgress(
        OutputInterface $output,
        \DateTime $startTime,
        $numberOfProducts,
        $processedProducts
    ) {
        $now = new \DateTime('now');
        $elapsedTime = $startTime->diff($now);
        $output->writeln(
            sprintf(
                "Progress: %d%% - %d / %d - Elapsed time %s",
                ceil(($processedProducts * 100) / $numberOfProducts),
                $processedProducts,
                $numberOfProducts,
                $elapsedTime->format('%H:%I:%S')
            )
        );
    }

    /**
     * Checks entities related to product values and removes them if they no longer exist.
     *
     * Checked entities are:
     * - attributes
     * - attribute options
     * - reference data (assets included)
     *
     * @param array $product
     *
     * @return array the changes to perform on current MongoBD document to fix missing related entities.
     */
    protected function checkValues(array $product)
    {
        if (!isset($product['values'])) {
            return $product;
        }

        foreach ($product['values'] as $valueIndex => $value) {
            $product = $this->checkAttribute($product, $valueIndex);

            if (!isset($product['values'][$valueIndex])) {
                continue;
            }

            if (isset($value['option'])) {
                $product = $this->checkAttributeOption($product, $valueIndex);
            }

            if (!isset($product['values'][$valueIndex])) {
                continue;
            }

            if (isset($value['optionIds'])) {
                $product = $this->checkAttributeOptions($product, $valueIndex);
            }

            if (!isset($product['values'][$valueIndex])) {
                continue;
            }

            $product = $this->checkReferenceDataFields($product, $valueIndex);
        }

        return $product;
    }

    /**
     * Checks if the reference data ID of a product values exists and removes it otherwise.
     *
     * @param array $product
     * @param int   $valueIndex
     *
     * @return bool
     */
    protected function checkReferenceDataFields(array $product, $valueIndex)
    {
        $referenceDataFields = $this->getReferenceDataFields();
        foreach ($referenceDataFields as $name => $referenceData) {
            if (isset($product['values'][$valueIndex][$referenceData['field']])) {
                $product = $this->checkReferenceDataField($referenceData, $product, $valueIndex);
            }
        }

        return $product;
    }

    /**
     * Checks if a reference data exists.
     *
     * @param array   $referenceData configuration (name, class) of the reference data
     * @param array   $product
     * @param integer $valueIndex    index of the prodcut value
     *
     * @return bool whether the reference data exists or not.
     */
    protected function checkReferenceDataField(array $referenceData, array $product, $valueIndex)
    {
        $referenceDataField = $product['values'][$valueIndex][$referenceData['field']];

        if (!is_array($referenceDataField)) {
            if (null !== $this->findEntity($referenceData['class'], $referenceDataField)) {
                $this->addMissingEntity($referenceData['class'], $referenceDataField);
            }

            unset($product['values'][$valueIndex]);
        } else {
            foreach ($referenceDataField as $key => $referenceDataId) {
                if (null !== $this->findEntity($referenceData['class'], $referenceDataId)) {
                    $this->addMissingEntity($referenceData['class'], $referenceDataId);
                }

                unset($product['values'][$valueIndex][$referenceData['field']][$key]);
            }
        }

        return $product;
    }

    /**
     * Adds a missing entity in the list
     *
     * @param string $entityName
     * @param int    $id
     */
    protected function addMissingEntity($entityName, $id)
    {
        if (!isset($this->missingEntities[$entityName])) {
            $this->missingEntities[$entityName] = [];
        }

        if (!isset($this->missingEntities[$entityName][$id])) {
            $this->missingEntities[$entityName][$id] = 0;
        }

        $this->missingEntities[$entityName][$id]++;
    }

    /**
     * Finds an entity given its class and its ID
     *
     * @param string $entityClass
     * @param int    $id
     *
     * @return null|object
     */
    protected function findEntity($entityClass, $id)
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager')->find($entityClass, $id);
    }

    /**
     * Checks if the attribute options ID of a product values exists and removes it otherwise.
     *
     * @param array $product
     * @param int   $valueIndex
     *
     * @return bool
     */
    protected function checkAttributeOptions(array $product, $valueIndex)
    {
        if (null === $this->optionIds) {
            $qb = $this->getContainer()->get('pim_catalog.repository.attribute_option')->createQueryBuilder('ao')
                ->select('ao.id');
            $results = $qb->getQuery()->getArrayResult();

            $this->optionIds = array_column($results, 'id');
        }

        $attributeId = $product['values'][$valueIndex]['attribute'];

        foreach ($product['values'][$valueIndex]['optionIds'] as $key => $optionId) {
            if (!in_array($optionId, $this->optionIds)) {
                $this->addMissingEntity(
                    $this->getContainer()->getParameter('pim_catalog.entity.attribute_option.class'),
                    sprintf(
                        'attribute %s > option %s',
                        isset($this->attributes[$attributeId]) ? $this->attributes[$attributeId]['code'] : 'unknown',
                        $optionId
                    )
                );

                unset($product['values'][$valueIndex]['optionIds'][$key]);
            }
        }

        return $product;
    }

    /**
     * Checks if the attribute option ID of a product value exists and removes it otherwise.
     *
     * @param array $product
     * @param int   $valueIndex
     *
     * @return bool
     */
    protected function checkAttributeOption(array $product, $valueIndex)
    {
        if (null === $this->optionIds) {
            $qb = $this->getContainer()->get('pim_catalog.repository.attribute_option')->createQueryBuilder('ao')
                ->select('ao.id');
            $results = $qb->getQuery()->getArrayResult();

            $this->optionIds = array_column($results, 'id');
        }

        $optionId = $product['values'][$valueIndex]['option'];
        $attributeId = $product['values'][$valueIndex]['attribute'];

        if (!in_array($optionId, $this->optionIds)) {
            $this->addMissingEntity(
                $this->getContainer()->getParameter('pim_catalog.entity.attribute_option.class'),
                sprintf(
                    'attribute %s > option %s',
                    isset($this->attributes[$attributeId]) ? $this->attributes[$attributeId]['code'] : 'unknown',
                    $optionId
                )
            );

            unset($product['values'][$valueIndex]);
        }

        return $product;
    }

    /**
     * Checks if the attribute ID of a product value exists and removes it otherwise.
     *
     * @param array $product
     * @param int   $valueIndex
     *
     * @return array
     */
    protected function checkAttribute(array $product, $valueIndex)
    {
        if (null === $this->attributes) {
            $qb = $this->getContainer()->get('doctrine.orm.entity_manager')->createQueryBuilder()
                ->select(['a.id', 'a.code'])
                ->from($this->getContainer()->getParameter('pim_catalog.entity.attribute.class'), 'a', 'a.id');

            $this->attributes = $qb->getQuery()->getArrayResult();
        }

        $attributeId = $product['values'][$valueIndex]['attribute'];

        if (!isset($this->attributes[$attributeId])) {
            $this->addMissingEntity(
                $this->getContainer()->getParameter('pim_catalog.entity.attribute.class'),
                $attributeId
            );

            unset($product['values'][$valueIndex]);
        }

        return $product;
    }

    /**
     * Checks if the category IDs exit and removes it from the product otherwise.
     *
     * @param array $product
     *
     * @return array
     */
    protected function checkCategories(array $product)
    {
        if (!isset($product['categoryIds'])) {
            return $product;
        }

        if (null === $this->categoryIds) {
            $qb = $this->getContainer()->get('pim_catalog.repository.category')->createQueryBuilder('c')
                ->select('c.id');
            $results = $qb->getQuery()->getArrayResult();

            $this->categoryIds = array_column($results, 'id');
        }

        foreach ($product['categoryIds'] as $key => $categoryId) {
            if (!in_array($categoryId, $this->categoryIds)) {
                $this->addMissingEntity(
                    $this->getContainer()->getParameter('pim_catalog.entity.category.class'),
                    $categoryId
                );

                unset($product['categoryIds'][$key]);
            }
        }

        return $product;
    }

    /**
     * Checks if the family ID exists and removes it from the product otherwise. It also migrate "label" field that
     * was renamed between 1.5 and 1.6.
     *
     * @param array $product
     *
     * @return array
     */
    protected function checkFamily(array $product)
    {
        if (!isset($product['family'])) {
            return $product;
        }

        if (isset($product['normalizedData']['family']['label'])) {
            $product['normalizedData']['family']['labels'] = $product['normalizedData']['family']['label'];
            $this->addMissingEntity(
                'Obsolete normalizedData.family.label field name',
                'N/A'
            );

            unset($product['normalizedData']['family']['label']);
        }

        if (null === $this->familyIds) {
            $qb = $this->getContainer()->get('pim_catalog.repository.family')->createQueryBuilder('f')
                ->select('f.id');
            $results = $qb->getQuery()->getArrayResult();

            $this->familyIds = array_column($results, 'id');
        }

        if (in_array($product['family'], $this->familyIds)) {
            return $product;
        }

        $this->addMissingEntity(
            $this->getContainer()->getParameter('pim_catalog.entity.family.class'),
            $product['family']
        );

        unset($product['family']);
        unset($product['normalizedData']['family']);
        unset($product['normalizedData']['completenesses']);
        unset($product['completenesses']);

        return $product;
    }

    /**
     * Checks if the association type ID exists and removes it from the product associations otherwise.
     *
     * @param array $product
     *
     * @return array
     */
    protected function checkAssociations(array $product)
    {
        if (!isset($product['associations']) || empty($product['associations'])) {
            return $product;
        }

        if (null === $this->associationTypeIds) {
            $qb = $this->getContainer()->get('pim_catalog.repository.association_type')->createQueryBuilder('at')
                ->select('at.id');
            $results = $qb->getQuery()->getArrayResult();

            $this->associationTypeIds = array_column($results, 'id');
        }

        $associations = [];
        foreach ($product['associations'] as $association) {
            if (!in_array($association['associationType'], $this->associationTypeIds)) {
                $this->addMissingEntity(
                    $this->getContainer()->getParameter('pim_catalog.entity.association_type.class'),
                    $association['associationType']
                );
            } else {
                $associations[] = $association;
            }
        }

        $product['associations'] = $associations;

        return $product;
    }

    /**
     * Search in Doctrine mapping what is the field name defined for all the reference data.
     *
     * @throws \LogicException if any error of mapping for the reference data.
     *
     * @return array
     */
    protected function getReferenceDataFields()
    {
        if (null === $this->referenceDataFields) {
            $valueClass = $this->getContainer()->getParameter('pim_catalog.entity.product_value.class');
            $manager = $this->getContainer()->get('doctrine_mongodb.odm.document_manager');

            $metadata = $manager->getClassMetadata($valueClass);
            $this->referenceDataFields = [];
            foreach ($this->getReferenceDataConfiguration() as $referenceData) {
                $referenceDataName = $referenceData->getName();
                if (ConfigurationInterface::TYPE_MULTI === $referenceData->getType()) {
                    $fieldName = $metadata->getFieldMapping($referenceDataName);

                    if (!isset($fieldName['idsField'])) {
                        throw new \LogicException(
                            sprintf(
                                'No field name defined for reference data "%s"',
                                $referenceDataName
                            )
                        );
                    }

                    $idField = $fieldName['idsField'];
                } else {
                    $idField = $referenceDataName;
                }

                $this->referenceDataFields[$referenceDataName] = ['field' => $idField, 'class' => $referenceData->getClass()];
            }
        }

        return $this->referenceDataFields;
    }

    /**
     * Get configuration for reference data.
     *
     * @return \Pim\Component\ReferenceData\Model\ConfigurationInterface[]
     */
    protected function getReferenceDataConfiguration()
    {
        $referenceDataRegistry = $this->getContainer()->get('pim_reference_data.registry');

        return $referenceDataRegistry->all();
    }

    /**
     * Get MongoDB Connection
     *
     * @return \MongoDB the database
     */
    protected function getMongoConnection()
    {
        $mongoConnection = $this->getContainer()->get('doctrine_mongodb.odm.default_connection');

        $dbName = $this->getContainer()->getParameter('mongodb_database');

        return $mongoConnection->getMongoClient()->$dbName;
    }
}
