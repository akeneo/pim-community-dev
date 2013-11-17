<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Inflector\Inflector;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\manager\ProductManager;

/**
 * Valid attribute creation (or update) processor
 *
 * Allow to bind input data to an attribute and validate it
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidAttributeCreationProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /**
     * Product manager
     *
     * @var ProductManager
     */
    protected $productManager;

    /**
     * Property for storing data during execution
     *
     * @var ArrayCollection
     */
    protected $data;

    /**
     * Property for storing valid attributes during execution
     *
     * @var ArrayCollection
     */
    protected $attributes;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * Constructor
     *
     * @param ProductManager     $productManager
     * @param ValidatorInterface $validator
     */
    public function __construct(
        ProductManager $productManager,
        ValidatorInterface $validator
    ) {
        $this->productManager = $productManager;
        $this->validator      = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * Receives an array of attributes and processes them
     *
     * @param mixed $data Data to be processed
     *
     * @return ProductAttribute[]
     */
    public function process($data)
    {
        $this->data   = new ArrayCollection($data);
        $this->attributes = new ArrayCollection();

        foreach ($this->data as $item) {
            $this->processItem($item);
        }

        return $this->attributes->toArray();
    }

    /**
     * If the attribute is valid, it is stored into the attribute property
     *
     * @param array $item
     */
    private function processItem($item)
    {
        $attribute = $this->getAttribute($item);

        foreach ($item as $key => $value) {
            if (preg_match('/^label-(.+)/', $key, $matches)) {
                $attribute->setLocale($matches[1]);
                $attribute->setLabel($value);
            }
        }
        $attribute->setLocale(null);

        if (empty($item['group'])) {
            $attribute->setGroup(null);
        } else {
            $group = $this->findAttributeGroup($item['group']);
            if (!$group) {
                throw new InvalidItemException(
                    sprintf('The "%s" group not exists.', $item['group']),
                    $item
                );
            }
        }

        $parameters = $this->prepareParameters($item);
        $attribute->setParameters($parameters);

        $violations = $this->validator->validate($attribute);
        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->stepExecution->addError((string) $violation);
            }

            return;
        } else {
            $this->attributes[] = $attribute;
        }
    }

    /**
     * Create an attribute or get it if already exists
     *
     * @param array $item
     *
     * @return Attribute
     */
    private function getAttribute(array $item)
    {
        $attribute = $this->findAttribute($item['code']);
        if (!$attribute) {
            $attribute = $this->productManager->createAttribute($item['type']);
            $attribute->setCode($item['code']);
        }

        return $attribute;
    }

    /**
     * Find attribute by code
     *
     * @param string $code
     *
     * @return ProductAttribute|null
     */
    private function findAttribute($code)
    {
        return $this
            ->productManager
            ->getStorageManager()
            ->getRepository('PimCatalogBundle:ProductAttribute')
            ->findOneBy(array('code' => $code));
    }

    /**
     * Find group by code
     *
     * @param string $code
     *
     * @return AttributeGroup|null
     */
    private function findAttributeGroup($code)
    {
        return $this
            ->productManager
            ->getStorageManager()
            ->getRepository('PimCatalogBundle:AttributeGroup')
            ->findOneBy(array('code' => $code));
    }

    /**
     * Prepare parameters
     *
     * @param array $data
     *
     * @return array
     */
    public function prepareParameters($data)
    {
        $parameters = array();
        $exclude = array('code', 'type', 'group', 'available_locales', 'localizable', 'scope', 'default_value');
        foreach (array_keys($data) as $key) {
            if (!in_array($key, $exclude) and !preg_match('/^label-(.+)/', $key, $matches)) {
                $parameters[Inflector::camelize($key)] = $data[$key];
            }
        }
        $parameters['dateMin']= (isset($parameters['dateMin'])) ? new \DateTime($parameters['dateMin']) : null;
        $parameters['dateMax']= (isset($parameters['dateMax'])) ? new \DateTime($parameters['dateMax']) : null;

        return $parameters;
    }
}
