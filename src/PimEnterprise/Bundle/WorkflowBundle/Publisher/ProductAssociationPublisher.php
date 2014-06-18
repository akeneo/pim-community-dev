<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Pim\Bundle\CatalogBundle\Model\AbstractAssociation;

use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

/**
 * Product association publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductAssociationPublisher implements PublisherInterface
{
    /** @var string */
    protected $publishClassName;

    /** @var PublishedProductRepositoryInterface*/
    protected $repository;

    /**
     * @param string                              $publishClassName
     * @param PublishedProductRepositoryInterface $repository
     */
    public function __construct($publishClassName, PublishedProductRepositoryInterface $repository)
    {
        $this->publishClassName = $publishClassName;
        $this->repository       = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        if (!isset($options['published'])) {
            throw new \LogicException('Published product must be known');
        }
        $published = $options['published'];
        $copiedAssociation = new $this->publishClassName();
        $copiedAssociation->setOwner($published);
        $copiedAssociation->setAssociationType($object->getAssociationType());

        $this->copyProducts($object, $copiedAssociation);
        $this->copyGroups($object, $copiedAssociation);

        return $copiedAssociation;
    }

    /**
     * Copy the products from original to published association
     *
     * @param AbstractAssociation $object
     * @param AbstractAssociation $copiedAssociation
     */
    protected function copyProducts(AbstractAssociation $object, AbstractAssociation $copiedAssociation)
    {
        $productIds = [];
        foreach ($object->getProducts() as $product) {
            $productIds[]= $product->getId();
        }
        $publishedProducts = $this->repository->findByOriginalProductIds($productIds);
        if (count($publishedProducts) > 0) {
            $copiedAssociation->setProducts($publishedProducts);
        }
    }

    /**
     * Copy the groups from original to published association
     *
     * @param AbstractAssociation $object
     * @param AbstractAssociation $copiedAssociation
     */
    protected function copyGroups(AbstractAssociation $object, AbstractAssociation $copiedAssociation)
    {
        foreach ($object->getGroups() as $group) {
            $copiedAssociation->addGroup($group);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof AbstractAssociation;
    }
}
