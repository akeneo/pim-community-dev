<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\ImportExportBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\ImportExportBundle\Transformer\Guesser\GuesserInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Attribute transformer
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMAttributeTransformer extends AbstractORMTransformer
{
    /**
     * @var ProductManager
     */
    protected $productManager;
    
    /**
     * Constructor
     * 
     * @param RegistryInterface $doctrine
     * @param PropertyAccessorInterface $propertyAccessor
     * @param GuesserInterface $guesser
     * @param ColumnInfoTransformerInterface $columnInfoTransformer
     * @param ProductManager $productManager
     */
    public function __construct(
        RegistryInterface $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $columnInfoTransformer,
        ProductManager $productManager
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $columnInfoTransformer);
        $this->productManager = $productManager;
    }

    /**
     * Transform an array in an attribute
     * 
     * @param array $data
     * 
     * @return \Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface
     */
    public function transform(array $data)
    {
        return $this->doTransform($this->productManager->getAttributeName(), $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($class, array $data)
    {
        return $this->productManager->createAttribute($data['type']);
    }
}
