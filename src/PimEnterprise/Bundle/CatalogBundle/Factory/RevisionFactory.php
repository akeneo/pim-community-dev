<?php

namespace PimEnterprise\Bundle\CatalogBundle\Factory;

use Symfony\Component\Security\Core\User\UserInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogBundle\Entity\Revision;

class RevisionFactory
{
    public function createRevision(ProductInterface $product, UserInterface $user, array $newValues)
    {
        $revision = new Revision();
        $revision->setProduct($product);
        $revision->setCreatedBy($user);
        $revision->setCreatedAt(new \DateTime());
        $revision->setNewValues($newValues);

        return $revision;
    }
}
