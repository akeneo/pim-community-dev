<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Valid attribute option creation (or update) processor
 *
 * Allow to bind input data to an option and validate it
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionProcessor extends AbstractEntityProcessor
{
    /**
     * @var string
     */
    protected $attributeClass;

    /**
     * Constructor
     *
     * @param EntityManager      $entityManager
     * @param ValidatorInterface $validator
     * @param string             $attributeClass
     */
    public function __construct(EntityManager $entityManager, ValidatorInterface $validator, $attributeClass)
    {
        parent::__construct($entityManager, $validator);
        $this->attributeClass = $attributeClass;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $option = $this->getOption($item);
        $option->setDefault((bool) $item['is_default']);
        $this->updateLabels($option, $item);

        $this->validate($option, $item);

        return $option;
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
    protected function getOption(array $item)
    {
        $attribute = $this->findAttribute($item['attribute']);
        if (!$attribute) {
            $this->skipItem($item, sprintf('The "%s" attribute not exists.', $item['attribute']));
        }
        if (!in_array($attribute->getBackendType(), array('option', 'options'))) {
            $this->skipItem($item, sprintf('The "%s" attribute cant contain option', $item['attribute']));
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
     * @return ProductAttributeInterface|null
     */
    protected function findAttribute($code)
    {
        return $this
            ->entityManager
            ->getRepository($this->attributeClass)
            ->findOneBy(array('code' => $code));
    }

    /**
     * Find option by code
     *
     * @param ProductAttributeInterface $attribute
     * @param string                    $code
     *
     * @return AttributeOption|null
     */
    protected function findOption(ProductAttributeInterface $attribute, $code)
    {
        return $this
            ->entityManager
            ->getRepository('PimCatalogBundle:AttributeOption')
            ->findOneBy(array('attribute' => $attribute->getId(), 'code' => $code));
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdentifier($entity)
    {
        return $entity->getAttribute()->getCode().'-'.$entity->getCode();
    }
}
