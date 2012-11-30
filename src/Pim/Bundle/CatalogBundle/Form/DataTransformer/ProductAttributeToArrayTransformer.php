<?php
namespace Pim\Bundle\CatalogBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Pim\Bundle\CatalogBundle\Doctrine\ProductManager;
use Bap\Bundle\FlexibleEntityBundle\Model\EntityAttribute as ProductAttribute;

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
    private $pm;

    /**
     * @param ProductManager $pm
     */
    public function __construct(ProductManager $pm)
    {
        $this->pm = $pm;
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
        $data['id']            =    $attribute->getId();
        $data['code']          =  $attribute->getCode();
        $data['title']         = $attribute->getTitle();
        $data['type']          = $attribute->getType();
        $data['scope']         = $attribute->getScope();
        $data['uniqueValue']   = $attribute->getUniqueValue();
        $data['valueRequired'] = $attribute->getValueRequired();
        $data['searchable']    = $attribute->getSearchable();
        $data['translatable']  = $attribute->getTranslatable;
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
        if ($setId) {
            $entity = $this->pm->getAttributeRepository()->find($attId);
        }
        if (!$entity) {
            $entity = $this->pm->getNewAttributeInstance();
        }

        // set general set information
        $entity->setCode($data['code']);
        $entity->setTitle($data['title']);

        // TODO other params
        // TODO : deal with optiohs

        return $entity;
    }
}
