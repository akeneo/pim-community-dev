<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Association flat denormalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationDenormalizer extends AbstractEntityDenormalizer
{
    /** @var string */
    protected $assocTypeClass;

    /** @var string */
    protected $groupClass;

    /** @var string */
    protected $productClass;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string          $entityClass
     * @param string          $assocTypeClass
     * @param string          $productClass
     * @param string          $groupClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        $entityClass,
        $assocTypeClass,
        $productClass,
        $groupClass
    ) {
        parent::__construct($managerRegistry, $entityClass);

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
            $identifiers = explode(',', $data);
            foreach ($identifiers as $identifier) {
                $group = $this->serializer->denormalize($identifier, $this->groupClass, $format);
                if (null !== $group) {
                    $association->addGroup($group);
                }
            }
        } else {
            // TODO: test should be in product denormalizer
            if (strlen($data) > 0) {
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
     * Get association type entity from its identifier
     *
     * @param string $identifier
     *
     * @return AssociationTypeInterface
     */
    protected function getAssociationType($identifier)
    {
        return $this->getAssociationTypeRepository()->findOneByIdentifier($identifier);
    }

    /**
     * @return AssociationTypeRepositoryInterface
     */
    protected function getAssociationTypeRepository()
    {
        return $this->managerRegistry->getRepository($this->assocTypeClass);
    }
}
