<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\ORM;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Cache\EntityCache;
use Pim\Bundle\VersioningBundle\EventListener\AddVersionListener;

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
     * @var AddVersionListener
     */
    protected $addVersionListener;

    /**
     * @var \Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute
     */
    protected $identifierAttribute;

    /**
     * @var EntityCache
     */
    protected $entityCache;

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
        'Pim\\Bundle\\FlexibleEntityBundle\\Entity\\Attribute',
        'Oro\\Bundle\\UserBundle\\Entity\\UserApi'
    );

    /**
     * @param ProductManager     $productManager
     * @param EntityCache        $entityCache
     * @param AddVersionListener $addVersionListener
     */
    public function __construct(
        ProductManager $productManager,
        EntityCache $entityCache,
        AddVersionListener $addVersionListener
    ) {
        $this->productManager     = $productManager;
        $this->entityCache        = $entityCache;
        $this->addVersionListener = $addVersionListener;
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
        $this->addVersionListener->setRealTimeVersioning($this->realTimeVersioning);
        foreach ($items as $item) {
            $this->incrementCount($item);
        }
        $this->productManager->handleAllMedia($items);
        $this->productManager->saveAll($items, false);

        $objectManager = $this->productManager->getObjectManager();

        foreach ($objectManager->getUnitOfWork()->getIdentityMap() as $className => $entities) {
            if (count($entities) && !in_array($className, $this->nonClearableEntities)) {
                $objectManager->clear($className);
            }
        }
        $this->entityCache->clear();
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
