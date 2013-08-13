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

    private $categories = array();
    private $attributes = array();

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
     * Goal is to transform an array like this:
     * array(
     *     'sku'        => 'sku-001',
     *     'name-en_US' => 'car',
     *     'name-fr_FR' => 'voiture,
     *     'categories' => 'cat_1,cat_2,cat3',
     * )
     *
     * into this:
     * array(
     *    'enabled' => '1',
     *    'values'  => array(
     *        'sku' => array(
     *             'varchar' => 'sku-001',
     *         ),
     *         'name_en_US' => array(
     *             'varchar' => 'car'
     *         ),
     *         'name_fr_FR' => array(
     *             'varchar' => 'voiture'
     *         ),
     *     ),
     *     'categories' => array(1, 2, 3)
     * )
     *
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
        foreach ($item as $code => $value) {
            $attributes[$code] = $this->getAttribute($code);
        }

        $product = $this->createProduct($attributes);
        $form    = $this->createAndSubmitForm($product, $attributes, $item);

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
     * Create a product using the initialized attributes
     *
     * @param array $item
     *
     * @return Product
     */
    private function createProduct(array $attributes)
    {
        $product = $this->productManager->createFlexible();

        foreach ($attributes as $attribute) {
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
    private function createAndSubmitForm(Product $product, array $attributes, array $item)
    {
        $values = array();
        $categories = array();
        foreach ($item as $code => $value) {
            if (isset($attributes[$code])) {
                $attribute = $attributes[$code];
                $key = str_replace('-', '_', $code);
                $values[$key][$attribute->getBackendType()] = $value;
            } elseif ($code === $this->categoriesColumn) {
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
        $parts = explode('-', $code);

        if (!array_key_exists($parts[0], $this->attributes)) {
            $this->attributes[$parts[0]] = $this->em
                ->getRepository('PimProductBundle:ProductAttribute')
                ->findOneBy(array('code' => $parts[0]));
        }

        return $this->attributes[$parts[0]];
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
