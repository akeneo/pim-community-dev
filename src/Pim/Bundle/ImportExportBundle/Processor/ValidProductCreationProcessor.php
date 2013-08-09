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

    private $attributes;

    public function __construct(EntityManager $em, FormFactoryInterface $formFactory, ProductManager $productManager)
    {
        $this->em          = $em;
        $this->formFactory = $formFactory;
        $this->productManager = $productManager;
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
            foreach ($item as $code) {
                $this->attributes[] = $this->getAttribute($code);
            }

            return;
        }

        $product = $this->productManager->createFlexible();
        foreach ($this->attributes as $attribute) {
            if (!$attribute) {
                continue;
            }
            $this->productManager->addAttributeToProduct($product, $attribute);
        }

        $values = array();
        foreach ($item as $index => $value) {
            if ($attribute = $this->attributes[$index]) {
                $values[$attribute->getCode()][$attribute->getBackendType()] = $value;
            }
        }

        $form = $this->formFactory->create('pim_product', $product, array(
            'csrf_protection' => false
        ));
        $form->submit(array(
            'enabled' => '1',
            'values'  => $values
        ));

        if (!$form->isValid()) {
            throw new \Exception($form->getErrorsAsString());
        }

        return $product;
    }

    public function getConfigurationFields()
    {
        return array();
    }

    private function getAttribute($code)
    {
        return $this->em
            ->getRepository('PimProductBundle:ProductAttribute')
            ->findOneBy(array('code' => $code));
    }
}
