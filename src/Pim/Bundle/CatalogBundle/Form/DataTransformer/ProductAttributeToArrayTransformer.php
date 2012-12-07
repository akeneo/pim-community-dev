<?php
namespace Pim\Bundle\CatalogBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Oro\Bundle\FlexibleEntityBundle\Model\EntityAttribute as ProductAttribute;

/**
 * Aims to transform array to attribute and reverse operation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeToArrayTransformer implements DataTransformerInterface
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
     * Transforms an object (attribute) to a array.
     *
     * @param ProductAttribute $attribute
     *
     * @return array
     */
    public function transform($attribute)
    {
        $data = array();
        // base data
        $data['id']            = $attribute->getId();
        $data['code']          = $attribute->getCode();
        $data['title']         = $attribute->getTitle();
        $data['type']          = $attribute->getType();
        $data['scope']         = $attribute->getScope();
        $data['uniqueValue']   = $attribute->getUniqueValue();
        $data['valueRequired'] = $attribute->getValueRequired();
        $data['searchable']    = $attribute->getSearchable();
        $data['translatable']  = $attribute->getTranslatable();
        // options
        $data['options']= array();
        foreach ($attribute->getOptions() as $option) {
            $optData = array();
            $optData[$option->getId()]= $option->getValue();
        }

        return $data;
    }

    /**
     * Transforms a array to an object (attribute).
     *
     * @param array $data
     *
     * @return ProductAttribute
     *
     * @throws TransformationFailedException if object (attribute) is not found.
     */
    public function reverseTransform($data)
    {
        // get or create set
        $attId = $data['id'];
        $entity = null;
        if ($attId) {
            $entity = $this->productManager->getAttributeRepository()->find($attId);
        }
        if (!$entity) {
            $entity = $this->productManager->getNewAttributeInstance();
        }

        // set general set information
        $entity->setCode($data['code']);
        $entity->setTitle($data['title']);
        $entity->setScope(1);
        $entity->setSearchable(false);
        $entity->setTranslatable(false);
        $entity->setUniqueValue(false);
        $entity->setType('');
        $entity->setValueRequired(false);

        // TODO other params
        // TODO : deal with optiohs

        return $entity;
    }
}
