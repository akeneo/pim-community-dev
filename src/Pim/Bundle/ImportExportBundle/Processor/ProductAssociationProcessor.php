<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
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
class ProductAssociationTypeProcessor extends AbstractEntityProcessor
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
        EntityManager $entityManager,
        ValidatorInterface $validator,
        ProductManager $productManager
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
            $this->skipItem($item, "The main product doesn't exist");
        }

        $associationsData = $this->prepareAssociationData($item);

        $productAssociations = array();
        foreach ($associationsData as $code => $associationData) {
            $associationType = $this->findAssociationType($code);
            if (!$associationType) {
                $this->skipItem($item, sprintf("The association type %s doesn't exist", $code));
            }

            $productAssociation = $this->getProductAssociation($product, $associationType);

            foreach ($associationData as $type => $relatedObjects) {
                if ($type === '_products') {
                    $this->addProducts($productAssociation, $relatedObjects, $product->getIdentifier(), $item);
                } else {
                    $this->addGroups($productAssociation, $relatedObjects, $item);
                }
            }
            $productAssociations[] = $productAssociation;
        }

        return $productAssociations;
    }

    /**
     * Add products to the association
     *
     * @param ProductAssociation $association
     * @param string             $identifiers
     * @param string             $productIdentifier
     * @param object             $item
     */
    protected function addProducts(ProductAssociation $association, $identifiers, $productIdentifier, $item)
    {
        $skus = explode(',', $identifiers);
        foreach ($skus as $sku) {
            $related = $this->findProduct($sku);
            if (!$related) {
                $this->skipItem($item, sprintf("The related product %s doesn't exist", $sku));
            } elseif ($related->getIdentifier() === $productIdentifier) {
                $this->skipItem($item, sprintf("The product can't %s be associated with itself", $sku));
            }
            $association->addProduct($related);
        }
    }

    /**
     * Add groups to the association
     *
     * @param ProductAssociation $association
     * @param string             $codes
     * @param object             $item
     */
    protected function addGroups(ProductAssociation $association, $codes, $item)
    {
        $groupCodes = explode(',', $codes);
        foreach ($groupCodes as $groupCode) {
            $related = $this->findGroup($groupCode);
            if (!$related) {
                $this->skipItem($item, sprintf("The related group %s doesn't exist", $groupCode));
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
                if (strpos($key, $type) !== false && $value !== '') {
                    $code = str_replace($type, '', $key);
                    if (!isset($associations[$code])) {
                        $associations[$code] = array();
                    }
                    $associations[$code][$type] = $value;
                }
            }
        }

        return $associations;
    }

    /**
     * Get the existing product association or create a new one
     *
     * @param ProductInterface $product
     * @param AssociationType  $association
     *
     * @return ProductAssociation
     */
    protected function getProductAssociation(ProductInterface $product, AssociationType $association)
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
            ->getRepository('Pim\Bundle\CatalogBundle\Entity\Group')
            ->findOneBy(array('code' => $code));
    }

    /**
     * Find association by code
     *
     * @param string $code
     *
     * @return AssociationType|null
     */
    protected function findAssociationType($code)
    {
        return $this
            ->entityManager
            ->getRepository('PimCatalogBundle:AssociationType')
            ->findOneBy(array('code' => $code));
    }
}
