<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;

/**
 * Valid attribute option creation (or update) processor
 *
 * Allow to bind input data to an option and validate it
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /**
     * Entity manager
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Property for storing data during execution
     *
     * @var ArrayCollection
     */
    protected $data;

    /**
     * Property for storing valid options during execution
     *
     * @var ArrayCollection
     */
    protected $options;

    /**
     * Constructor
     *
     * @param EntityManager      $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManager $entityManager,
        ValidatorInterface $validator
    ) {
        $this->entityManager = $entityManager;
        $this->validator     = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * Receives an array of options and processes them
     *
     * @param mixed $data Data to be processed
     *
     * @return ProductAttribute[]
     */
    public function process($data)
    {
        $this->data   = new ArrayCollection($data);
        $this->options = new ArrayCollection();

        foreach ($this->data as $item) {
            $this->processItem($item);
        }

        return $this->options->toArray();
    }

    /**
     * If the option is valid, it is stored into the option property
     *
     * @param array $item
     *
     * @throws InvalidItemException
     */
    private function processItem($item)
    {
        $option = $this->getOption($item);
        $option->setDefault((bool) $item['is_default']);
        $this->updateLabels($option, $item);

        $violations = $this->validator->validate($option);
        if ($violations->count() > 0) {
            $messages = array();
            foreach ($violations as $violation) {
                $messages[]= (string) $violation;
            }
            throw new InvalidItemException(implode(', ', $messages), $item);

        } else {
            $this->options[] = $option;
        }
    }

    /**
     * Set labels
     *
     * @param AttributeOption $option
     * @param array           $item
     */
    protected function updateLabels(AttributeOption $option, array $item)
    {
        foreach ($item as $key => $data) {
            if (preg_match('/^label-(.+)/', $key, $matches)) {
                $locale = $matches[1];
                $option->setLocale($locale);
                $optValue = $option->getOptionValue();
                if (!$optValue) {
                    $optValue = new AttributeOptionValue();
                    $optValue->setLocale($locale);
                    $option->addOptionValue($optValue);
                }
                $optValue->setValue($data);
            }
        }
        $option->setLocale(null);
    }

    /**
     * Create an option or get it if already exists
     *
     * @param array $item
     *
     * @return AttributeOption
     */
    private function getOption(array $item)
    {
        $attribute = $this->findAttribute($item['attribute']);
        if (!$attribute) {
            throw new InvalidItemException(
                sprintf('The "%s" attribute not exists.', $item['attribute']),
                $item
            );
        }
        if (!in_array($attribute->getBackendType(), array('option', 'options'))) {
            throw new InvalidItemException(
                sprintf('The "%s" attribute cant contain option', $item['attribute']),
                $item
            );
        }

        $option = $this->findOption($attribute, $item['code']);
        if (!$option) {
            $option = new AttributeOption();
            $option->setCode($item['code']);
            $option->setAttribute($attribute);
        }

        return $option;
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
            ->entityManager
            ->getRepository('PimCatalogBundle:ProductAttribute')
            ->findOneBy(array('code' => $code));
    }

    /**
     * Find option by code
     *
     * @param ProductAttribute $attribute
     * @param string           $code
     *
     * @return AttributeOption|null
     */
    private function findOption(ProductAttribute $attribute, $code)
    {
        return $this
            ->entityManager
            ->getRepository('PimCatalogBundle:AttributeOption')
            ->findOneBy(array('attribute' => $attribute->getId(), 'code' => $code));
    }
}
