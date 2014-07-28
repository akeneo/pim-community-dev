<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AssociationDenormalizer extends AbstractEntityDenormalizer
{
    /** @var string */
    protected $assocTypeClass;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $groupClass;

    /**
     * @param ManagerRegistry $registry
     * @param string          $entityClass
     * @param string          $assocTypeClass
     * @param string          $productClass
     * @param string          $groupClass
     */
    public function __construct(ManagerRegistry $registry, $entityClass, $assocTypeClass, $productClass, $groupClass)
    {
        parent::__construct($registry, $entityClass);

        $this->assocTypeClass = $assocTypeClass;
        $this->productClass   = $productClass;
        $this->groupClass     = $groupClass;
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
     * @param array $data
     * @param       $format
     * @param array $context
     *
     * @return object
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
            throw new \Exception(
                'Association entity or association type code should be passed in context"'
            );
        }

        if ('groups' === $context['part']) {
            $identifiers = explode(',', $data);
            foreach ($identifiers as $identifier) {
                $group = $this->serializer->denormalize($identifier, $this->groupClass, $format);
                if (null !== $group) {
                    $association->addGroup($group);
                }
            }
        } else {
            if (strlen($data) > 0) { // TODO: test should be in product denormalizer
                $identifiers = explode(',', $data);
                foreach ($identifiers as $identifier) {
                    $product = $this->serializer->denormalize($identifier, $this->productClass, $format);
                    if (null !== $product) {
                        $association->addProduct($product);
                    }
                }
            }
        }

        return $association;
    }

    /**
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
