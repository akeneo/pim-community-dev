<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\Product;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\AbstractAssociation;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\PublisherInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Product association publisher
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class AssociationPublisher implements PublisherInterface
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
        $this->repository = $repository;
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
        $copiedAssociation = $this->createNewPublishedAssociation();
        $copiedAssociation->setAssociationType($object->getAssociationType());
        $copiedAssociation->setOwner($published);

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
        $products = $object->getProducts();
        if (0 === $products->count()) {
            return;
        }

        $publishedProducts = $this->repository->findByOriginalProducts($products->toArray());
        if (count($publishedProducts) > 0) {
            if (is_array($publishedProducts)) {
                $publishedProducts = new ArrayCollection($publishedProducts);
            }
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
     * @return PublishedProductAssociation
     */
    protected function createNewPublishedAssociation()
    {
        return new $this->publishClassName();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof AbstractAssociation;
    }
}
