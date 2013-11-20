<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Entity\Association;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAssociation;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Valid product association creation (or update) processor
 *
 * Allow to bind input data to a product association and validate it
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationProcessor extends AbstractEntityProcessor
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * Constructor
     *
     * @param EntityManager      $entityManager
     * @param ValidatorInterface $validator
     * @param ProductManager     $productManager
     */
    public function __construct(
        EntityManager      $entityManager,
        ValidatorInterface $validator,
        ProductManager     $productManager
    ) {
        parent::__construct($entityManager, $validator);
        $this->productManager = $productManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $identifier = $this->productManager->getIdentifierAttribute();
        $sku = $item[$identifier->getCode()];
        $product = $this->findProduct($sku);
        if (!$product) {
            throw new InvalidItemException("The main product doesn't exist", $item);
        }

        $associationsData = $this->prepareAssociationData($item);

        $productAssociations = array();
        foreach ($associationsData as $code => $associationData) {
            $association = $this->findAssociation($code);
            if (!$association) {
                throw new InvalidItemException(sprintf("The association %s doesn't exist", $code), $item);
            }

            $productAssociation = $this->getProductAssociation($product, $association);

            foreach ($associationData as $type => $relatedObjects) {
                if ($type == '_products') {
                    $this->addProducts($productAssociation, $relatedObjects, $product->getIdentifier());
                } else {
                    $this->addGroups($productAssociation, $relatedObjects);
                }
            }
            $productAssociations[]= $productAssociation;
        }

        return $productAssociations;
    }

    /**
     * Add products to the association
     *
     * @param ProductAssociation $association
     * @param string             $identifiers
     * @param string             $productIdentifier
     */
    protected function addProducts(ProductAssociation $association, $identifiers, $productIdentifier)
    {
        $skus = explode(',', $identifiers);
        foreach ($skus as $sku) {
            $related = $this->findProduct($sku);
            if (!$related) {
                throw new InvalidItemException(
                    sprintf("The related product %s doesn't exist", $sku),
                    $item
                );
            } elseif ($related->getIdentifier() === $productIdentifier) {
                throw new InvalidItemException(
                    sprintf("The product can't %s be associated with itself", $sku),
                    $item
                );
            }
            $association->addProduct($related);
        }
    }

    /**
     * Add groups to the association
     *
     * @param ProductAssociation $association
     * @param string             $codes
     */
    protected function addGroups(ProductAssociation $association, $codes)
    {
        $groupCodes = explode(',', $codes);
        foreach ($groupCodes as $groupCode) {
            $related = $this->findGroup($groupCode);
            if (!$related) {
                throw new InvalidItemException(
                    sprintf("The related group %s doesn't exist", $groupCode),
                    $item
                );
            }
            $association->addGroup($related);
        }
    }

    /**
     * Prepare product associations data
     *
     * @param array $item
     *
     * @return array
     */
    protected function prepareAssociationData($item)
    {
        $relatedTo = array('_groups', '_products');
        $associations = array();

        foreach ($item as $key => $value) {
            foreach ($relatedTo as $type) {
                if (strpos($key, $type) !== false && $value != '') {
                    $code = str_replace($type, '', $key);
                    if (!isset($associations[$code])) {
                        $associations[$code]= array();
                    }
                    $associations[$code][$type]= $value;
                }
            }
        }

        return $associations;
    }

    /**
     * Get the existing product association or create a new one
     *
     * @param ProductInterface $product
     * @param Association      $association
     *
     * @return ProductAssociation
     */
    protected function getProductAssociation(ProductInterface $product, Association $association)
    {
        $productAssociation = $product->getProductAssociationForAssociation($association);
        if (!$productAssociation) {
            $productAssociation = new ProductAssociation();
            $productAssociation->setOwner($product);
            $productAssociation->setAssociation($association);
        }

        return $productAssociation;
    }

    /**
     * Find product by identifier
     *
     * @param string $identifier
     *
     * @return ProductInterface|null
     */
    protected function findProduct($identifier)
    {
        return $this->productManager->findByIdentifier($identifier);
    }

    /**
     * Find group by code
     *
     * @param string $code
     *
     * @return Group|null
     */
    private function findGroup($code)
    {
        return $this
            ->entityManager
            ->getRepository('PimCatalogBundle:Group')
            ->findOneBy(array('code' => $code));
    }

    /**
     * Find association by code
     *
     * @param string $code
     *
     * @return Association|null
     */
    protected function findAssociation($code)
    {
        return $this
            ->entityManager
            ->getRepository('PimCatalogBundle:Association')
            ->findOneBy(array('code' => $code));
    }
}
