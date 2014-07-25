<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AssociationDenormalizer extends AbstractEntityDenormalizer
{
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $association = $context['entity'];

        if ('groups' === $context['part']) {
            if (strlen($data) > 0) {
                $identifiers = explode(',', $data);
                foreach ($identifiers as $identifier) {
                    $association->addGroup(
                        $this->getGroup($identifier)
                    );
                }
            }
        } else {
            if (strlen($data) > 0) {
                $identifiers = explode(',', $data);
                foreach ($identifiers as $identifier) {
                    $association->addProduct(
                        $this->getProduct($identifier)
                    );
                }
            }
        }

        return $association;
    }

    /**
     * @param $associationTypeCode
     */
    protected function getAssociationType($associationTypeCode)
    {
        return $this->getAssociationTypeRepository()->findByReference($associationTypeCode);
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository
     *
     * TODO: Remove hardcoded data class
     */
    protected function getAssociationTypeRepository()
    {
        return $this->managerRegistry->getRepository('Pim\Bundle\CatalogBundle\Entity\AssociationType');
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository
     */
    protected function getGroupRepository()
    {
        return $this->managerRegistry->getRepository('Pim\Bundle\CatalogBundle\Entity\Group');
    }

    protected function getGroup($identifier)
    {
        return $this->getGroupRepository()->findByReference($identifier);
    }

    protected function getProductRepository()
    {
        return $this->managerRegistry->getRepository('Pim\Bundle\CatalogBundle\Model\Product');
    }

    protected function getProduct($identifier)
    {
        return $this->getProductRepository()->findByReference($identifier);
    }
}
