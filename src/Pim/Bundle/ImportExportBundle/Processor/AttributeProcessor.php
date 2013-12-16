<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Inflector\Inflector;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Valid attribute creation (or update) processor
 *
 * Allow to bind input data to an attribute and validate it
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeProcessor extends AbstractEntityProcessor
{
    /**
     * Product manager
     *
     * @var ProductManager
     */
    protected $productManager;

    /**
     * Constructor
     * @param EntityManager      $manager
     * @param ValidatorInterface $validator
     * @param ProductManager     $productManager
     */
    public function __construct(
        EntityManager $manager,
        ValidatorInterface $validator,
        ProductManager $productManager
    ) {
        parent::__construct($manager, $validator);
        $this->productManager = $productManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $attribute = $this->getAttribute($item);
        $this->updateLabels($attribute, $item);
        $this->updateGroup($attribute, $item);
        $this->updateParameters($attribute, $item);

        $this->validate($attribute, $item);

        return $attribute;
    }

    /**
     * Set labels
     *
     * @param ProductAttributeInterface $attribute
     * @param array                     $item
     */
    protected function updateLabels(ProductAttributeInterface $attribute, array $item)
    {
        foreach ($item as $key => $value) {
            if (preg_match('/^label-(.+)/', $key, $matches)) {
                $attribute->setLocale($matches[1]);
                $attribute->setLabel($value);
            }
        }
        $attribute->setLocale(null);
    }

    /**
     * Set group
     *
     * @param ProductAttributeInterface $attribute
     * @param array                     $item
     *
     * @throws InvalidItemException
     */
    protected function updateGroup(ProductAttributeInterface $attribute, array $item)
    {
        if (empty($item['group']) || $item['group'] == AttributeGroup::DEFAULT_GROUP_CODE) {
            $attribute->setGroup(null);
        } else {
            $group = $this->findAttributeGroup($item['group']);
            if (!$group) {
                $this->skipItem($item, sprintf('The "%s" group not exists.', $item['group']));
            }
            $attribute->setGroup($group);
        }
    }

    /**
     * Set parameters
     *
     * @param ProductAttributeInterface $attribute
     * @param array                     $item
     */
    protected function updateParameters(ProductAttributeInterface $attribute, array $item)
    {
        $parameters = $this->prepareParameters($item);
        $attribute->setParameters($parameters);
    }

    /**
     * Prepare parameters
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareParameters($data)
    {
        $parameters = array();

        $booleanParams = array('useable_as_grid_column', 'useable_as_grid_filter', 'unique');
        foreach ($booleanParams as $key) {
            $parameters[Inflector::camelize($key)] = (bool) $data[$key];
        }

        $strParams = array('allowed_extensions', 'date_type', 'metric_family', 'default_metric_unit');
        foreach ($strParams as $key) {
            $parameters[Inflector::camelize($key)] = (string) $data[$key];
        }

        return $parameters;
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
            $attribute->setTranslatable((bool) $item['is_translatable']);
            $attribute->setScopable((bool) $item['is_scopable']);
        }

        return $attribute;
    }

    /**
     * Find attribute by code
     *
     * @param string $code
     *
     * @return ProductAttributeInterface|null
     */
    private function findAttribute($code)
    {
        return $this
            ->entityManager
            ->getRepository($this->productManager->getAttributeName())
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
            ->entityManager
            ->getRepository('PimCatalogBundle:AttributeGroup')
            ->findOneBy(array('code' => $code));
    }
}
