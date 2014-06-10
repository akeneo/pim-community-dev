<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\Doctrine;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Pim\Bundle\TransformBundle\Cache\DoctrineCache;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;

/**
 * Product writer using ORM method
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var VersionManager
     */
    protected $versionManager;

    /**
     * @var SmartManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var \Pim\Bundle\CatalogBundle\Model\AbstractAttribute
     */
    protected $identifierAttribute;

    /**
     * @var DoctrineCache
     */
    protected $doctrineCache;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * @var boolean
     */
    protected $realTimeVersioning = true;

    /**
     * Entities which should not be cleared on flush
     *
     * @var array
     */
    protected $nonClearableEntities = array(
        'Akeneo\\Bundle\\BatchBundle\\Entity\\JobExecution',
        'Akeneo\\Bundle\\BatchBundle\\Entity\\JobInstance',
        'Pim\\Bundle\\CatalogBundle\\Entity\\Family',
        'Pim\\Bundle\\CatalogBundle\\Entity\\Channel',
        'Pim\\Bundle\\CatalogBundle\\Entity\\Locale',
        'Pim\\Bundle\\CatalogBundle\\Entity\\Currency',
        'Akeneo\\Bundle\\BatchBundle\\Entity\\StepExecution',
        'Oro\\Bundle\\UserBundle\\Entity\\User',
        'Oro\\Bundle\\OrganizationBundle\\Entity\\BusinessUnit',
        'Oro\\Bundle\\UserBundle\\Entity\\UserApi'
    );

    /**
     * @param ProductManager $productManager
     * @param DoctrineCache  $doctrineCache
     * @param VersionManager $versionManager
     * @param SmartManagerRegistry $managerRegistry
     */
    public function __construct(
        ProductManager $productManager,
        DoctrineCache $doctrineCache,
        VersionManager $versionManager,
        SmartManagerRegistry $managerRegistry
    ) {
        $this->productManager  = $productManager;
        $this->doctrineCache   = $doctrineCache;
        $this->versionManager  = $versionManager;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Adds a non clearable entity
     *
     * @param string $class
     */
    public function addNonClearableEntity($class)
    {
        $this->nonClearableEntities[] = $class;
    }

    /**
     * Set the list of non clearable entities class.
     * Allow override of the default list.
     *
     * @param array $classesList
     */
    public function setNonClearableEntities($classesList)
    {
        $this->nonClearableEntities = $classesList;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'realTimeVersioning' => array(
                'type'    => 'switch',
                'options' => array(
                    'label' => 'pim_base_connector.import.realTimeVersioning.label',
                    'help'  => 'pim_base_connector.import.realTimeVersioning.help'
                )
            )
        );
    }

    /**
     * Set real time versioning
     *
     * @param boolean $realTime
     */
    public function setRealTimeVersioning($realTime)
    {
        $this->realTimeVersioning = $realTime;
    }

    /**
     * Is real time versioning
     *
     * @return boolean
     */
    public function isRealTimeVersioning()
    {
        return $this->realTimeVersioning;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $this->versionManager->setRealTimeVersioning($this->realTimeVersioning);
        foreach ($items as $item) {
            $this->incrementCount($item);
        }
        $this->productManager->handleAllMedia($items);
        $this->productManager->saveAll($items, false);

        $this->clear();
    }

    /**
     * Clear the Unit of Work of the manager(s) from the clearable entities
     * between batch writes
     */
    protected function clear()
    {
        foreach ($this->managerRegistry->getManagers() as $objectManager) {

            $clearAll = true;
            foreach ($objectManager->getUnitOfWork()->getIdentityMap() as $className => $entities) {
                if (count($entities) > 0) {
                    if (in_array($className, $this->nonClearableEntities)) {
                        $clearAll = false;
                    } else {
                        $objectManager->clear($className);
                    }
                }

                if ($clearAll) {
                    $objectManager->clear();
                }
            }
        }
        $this->doctrineCache->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param ProductInterface $product
     */
    protected function incrementCount(ProductInterface $product)
    {
        if ($product->getId()) {
            $this->stepExecution->incrementSummaryInfo('update');
        } else {
            $this->stepExecution->incrementSummaryInfo('create');
        }
    }
}
