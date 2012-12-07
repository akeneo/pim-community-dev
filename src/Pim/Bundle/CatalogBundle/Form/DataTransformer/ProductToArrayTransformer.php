<?php
namespace Pim\Bundle\CatalogBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Bap\Bundle\FlexibleEntityBundle\Model\EntitySet as ProductSet;

/**
 * Aims to transform array of values to product and reverse operation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductToArrayTransformer implements DataTransformerInterface
{
    /**
     * @var ProductManager
     */
    private $productManager;

    /**
     * @param ProductManager $productManager
     */
    public function __construct(ProductManager $productManager)
    {
        $this->productManager = $productManager;
    }

    /**
     * Transforms an object (product set) to a array.
     *
     * @param Product $product
     *
     * @return array
     */
    public function transform($product)
    {
        $data = array();
        // base data
        $data['id'] = $product->getId();
        $data['sku'] = $product->getSku();
        // values
        $data['values']= array();
        foreach ($product->getValues() as $value) {
            $data['values'][$value->getAttribute()->getCode()]= $value->getData();
        }

        return $data;
    }

    /**
     * Transforms a array to an object (product).
     *
     * @param array $data
     *
     * @return ProductSet
     *
     * @throws TransformationFailedException if object (set) is not found.
     */
    public function reverseTransform($data)
    {
        // get or create set
        $productId = $data['id'];
        if ($productId) {
            $product = $this->productManager->getEntityRepository()->find($productId);
        } else {
            $product = $this->productManager->getNewEntityInstance();
        }
        $product->setSku($data['sku']);

        // change values
        $updated = array();
        foreach ($product->getValues() as $value) {
            $attributeCode = $value->getAttribute()->getCode();
            $newData = $data['values'][$attributeCode];
            $value->setData($newData);
            $updated[]= $attributeCode;
        }

        // add values
        foreach ($data['values'] as $code => $data) {
            if (!in_array($code, $updated)) {
                $attribute = $this->productManager->getAttributeRepository()->findOneByCode($code);
                $newValue = $this->productManager->getNewAttributeValueInstance();
                $newValue->setAttribute($attribute);
                $newValue->setData($data);
                $product->addValue($newValue);
            }
        }

        return $product;
    }
}
