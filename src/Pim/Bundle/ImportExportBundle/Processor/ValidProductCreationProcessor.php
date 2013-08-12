<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;
use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Pim\Bundle\ProductBundle\Manager\ProductManager;

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
    private $categoriesColumnIndex;

    public function __construct(EntityManager $em, FormFactoryInterface $formFactory, ProductManager $productManager)
    {
        $this->em             = $em;
        $this->formFactory    = $formFactory;
        $this->productManager = $productManager;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setCategoriesColumn($categoriesColumn)
    {
        $this->categoriesColumn = $categoriesColumn;
    }

    public function getCategoriesColumn()
    {
        return $this->categoriesColumn;
    }

    public function setCategoriesDelimiter($categoriesDelimiter)
    {
        $this->categoriesDelimiter = $categoriesDelimiter;
    }

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
     *     )
     * )
     * and to bind it to the ProductType
     */
    public function process($item)
    {
        if (!$this->attributes) {
            // First processed item is the attributes
            foreach ($item as $index => $code) {
                if ($this->categoriesColumn === $code) {
                    $this->categoriesColumnIndex = $index;
                }
                $this->attributes[] = $this->getAttribute($code);
            }

            return;
        }

        $product = $this->productManager->createFlexible();

        foreach ($this->attributes as $attribute) {
            if (!$attribute) {
                // We ignore attribute that doesn't exist in the PIM
                continue;
            }
            $this->productManager->addAttributeToProduct($product, $attribute);
        }

        $values = array();
        $categories = array();
        foreach ($item as $index => $value) {
            if ($attribute = $this->attributes[$index]) {
                $values[$attribute->getCode()][$attribute->getBackendType()] = $value;
            } elseif ($index === $this->categoriesColumnIndex) {
                $categories = $this->getCategoryIds($value);
            }
        }

        // $form = $this->createAndSubmitForm($product, $values, $categories);
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

        if (!$form->isValid()) {
            throw new \Exception($form->getErrorsAsString());
        }

        return $product;
    }

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
        return $this->em
            ->getRepository('PimProductBundle:Category')
            ->findOneBy(array('code' => $code));
    }
}
