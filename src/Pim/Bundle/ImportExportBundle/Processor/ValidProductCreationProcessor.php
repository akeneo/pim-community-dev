<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;
use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Pim\Bundle\ProductBundle\Manager\ProductManager;
use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ImportExportBundle\Exception\InvalidObjectException;

/**
 * Product form processor
 * Allows to bind data into a product and validate them
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidProductCreationProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    protected $em;
    protected $formFactory;
    protected $productManager;

    protected $enabled             = true;
    protected $categoriesColumn    = 'categories';
    protected $categoriesDelimiter = ',';

    private $attributes;
    private $categories = array();
    private $categoriesColumnIndex;

    public function __construct(EntityManager $em, FormFactoryInterface $formFactory, ProductManager $productManager)
    {
        $this->em             = $em;
        $this->formFactory    = $formFactory;
        $this->productManager = $productManager;
    }

    /**
     * Set wether or not the created product should be activated or not
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Wether or not the created product should be activated or not
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set the categories column
     *
     * @param string $categoriesColumn
     */
    public function setCategoriesColumn($categoriesColumn)
    {
        $this->categoriesColumn = $categoriesColumn;
    }

    /**
     * Get the categories delimiter
     *
     * @return string
     */
    public function getCategoriesColumn()
    {
        return $this->categoriesColumn;
    }

    /**
     * Set the categories delimiter
     *
     * @param string $categoriesDelimiter
     */
    public function setCategoriesDelimiter($categoriesDelimiter)
    {
        $this->categoriesDelimiter = $categoriesDelimiter;
    }

    /**
     * Get the categories delimiter
     *
     * @return string
     */
    public function getCategoriesDelimiter()
    {
        return $this->categoriesDelimiter;
    }

    /**
     * Goal is to create an array structured like this:
     * array(
     *    'enabled' => '1',
     *    'values'  => array(
     *        'sku' => array(
     *            'varchar' => 'sku-001',
     *         )
     *     ),
     *     'categories' => array(1, 2, 3)
     * )
     * and to bind it to the ProductType.
     *
     * @param mixed $item item to be processed
     *
     * @return null|Product
     *
     * @throw Exception when validation errors happenned
     */
    public function process($item)
    {
        if (!$this->attributes) {
            $this->initializeAttributes($item);

            return;
        }

        $product = $this->createProduct();
        $form    = $this->createAndSubmitForm($product, $item);

        if (!$form->isValid()) {
            throw new InvalidObjectException($form);
        }

        return $product;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'enabled' => array(
                'type' => 'checkbox',
            ),
            'categoriesColumn' => array(),
            'categoriesDelimiter' => array(),
        );
    }

    /**
     * Initialize the attributes using the first processed item
     *
     * @param array $item the processed item
     */
    private function initializeAttributes(array $item)
    {
        foreach ($item as $index => $code) {
            if ($this->categoriesColumn === $code) {
                $this->categoriesColumnIndex = $index;
            }
            $this->attributes[] = $this->getAttribute($code);
        }
    }

    /**
     * Create a product using the initialized attributes
     *
     * @param array $item
     *
     * @return Product
     */
    private function createProduct()
    {
        $product = $this->productManager->createFlexible();

        foreach ($this->attributes as $attribute) {
            if (!$attribute) {
                // We ignore attribute that doesn't exist in the PIM
                continue;
            }
            $this->productManager->addAttributeToProduct($product, $attribute);
        }

        return $product;
    }

    /**
     * Create and submit the product form
     *
     * @param Product     the product to which bind the data
     * @param array $item the processed item
     *
     * @return FormInterface
     */
    private function createAndSubmitForm(Product $product, array $item)
    {
        $values = array();
        $categories = array();
        foreach ($item as $index => $value) {
            if ($attribute = $this->attributes[$index]) {
                $values[$attribute->getCode()][$attribute->getBackendType()] = $value;
            } elseif ($index === $this->categoriesColumnIndex) {
                $categories = $this->getCategoryIds($value);
            }
        }

        $form = $this->formFactory->create(
            'pim_product',
            $product,
            array(
                'csrf_protection' => false,
                'withCategories'  => true,
            )
        );

        $form->submit(
            array(
                'enabled'    => (string) (int) $this->enabled,
                'values'     => $values,
                'categories' => $categories,
            )
        );

        return $form;
    }

    private function getCategoryIds($codes)
    {
        $ids = array();
        foreach (explode($this->categoriesDelimiter, $codes) as $code) {
            if ($category = $this->getCategory($code)) {
                $ids[] = $category->getId();
            }
        }

        return $ids;
    }

    private function getAttribute($code)
    {
        return $this->em
            ->getRepository('PimProductBundle:ProductAttribute')
            ->findOneBy(array('code' => $code));
    }

    private function getCategory($code)
    {
        if (!array_key_exists($code, $this->categories)) {
            $this->categories[$code] = $this->em
                ->getRepository('PimProductBundle:Category')
                ->findOneBy(array('code' => $code));
        }

        return $this->categories[$code];
    }
}
