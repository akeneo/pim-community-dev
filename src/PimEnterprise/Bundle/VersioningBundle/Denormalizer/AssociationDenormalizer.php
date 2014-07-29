<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Association flat denormalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AssociationDenormalizer extends AbstractEntityDenormalizer
{
    /**
     * @param ManagerRegistry $managerRegistry
     * @param string          $entityClass
     * @param string          $assocTypeClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $entityClass,
        $assocTypeClass
    ) {
        parent::__construct($managerRegistry, $entityClass);

        $this->assocTypeClass = $assocTypeClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!isset($context['part']) || !in_array($context['part'], ['groups', 'products'])) {
            throw new \Exception(
                'Missing key "part" in context explaining if denormalizing groups or products part of the association'
            );
        }

        return $this->doDenormalize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDenormalize($data, $format, array $context)
    {
        if (isset($context['entity']) && null !== $context['entity']) {
            $association = $context['entity'];
            unset($context['entity']);
        } elseif (isset($context['association_type_code'])) {
            $association = $this->createEntity();
            $association->setAssociationType(
                $this->getAssociationType($context['association_type_code'])
            );
        } else {
            throw new InvalidArgumentException(
                'Association entity or association type code should be passed in context"'
            );
        }

        if ('groups' === $context['part']) {
            $groupClass = $this->getTargetClass('groups');
            $identifiers = explode(',', $data);
            foreach ($identifiers as $identifier) {
                $group = $this->serializer->denormalize($identifier, $groupClass, $format);
                if (null !== $group) {
                    $association->addGroup($group);
                }
            }
        } else {
            if (strlen($data) > 0) { // TODO: test should be in product denormalizer
                $productClass = $this->getTargetClass('products');
                $identifiers = explode(',', $data);
                foreach ($identifiers as $identifier) {
                    $product = $this->serializer->denormalize($identifier, $productClass, $format);
                    if (null !== $product) {
                        $association->addProduct($product);
                    }
                }
            }
        }

        return $association;
    }

    /**
     * Get association type entity from its identifier
     *
     * @param string $identifier
     *
     * @return AssociationType
     */
    protected function getAssociationType($identifier)
    {
        return $this->getAssociationTypeRepository()->findByReference($identifier);
    }

    /**
     * @return \Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository
     */
    protected function getAssociationTypeRepository()
    {
        return $this->managerRegistry->getRepository($this->assocTypeClass);
    }
}
