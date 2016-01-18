<?php

namespace Pim\Bundle\TransformBundle\Transformer;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Transforms associations
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)p
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationTransformer extends EntityTransformer
{
    /**
     * @var string
     */
    protected $productClass;

    /**
     * @var string
     */
    protected $associationTypeClass;

    /**
     * Constructor
     *
     * @param ManagerRegistry                $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $colInfoTransformer
     * @param string                         $productClass
     * @param string                         $associationTypeClass
     */
    public function __construct(
        ManagerRegistry $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $colInfoTransformer,
        $productClass,
        $associationTypeClass
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $colInfoTransformer);
        $this->productClass         = $productClass;
        $this->associationTypeClass = $associationTypeClass;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($class, array $data, array $defaults = array())
    {
        $entity = parent::transform($class, $data, $defaults);
        $objectManager = $this->doctrine->getManagerForClass($this->productClass);
        $objectManager->persist($entity->getOwner());

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($class, array $data)
    {
        if (!isset($data['owner'])) {
            throw new InvalidItemException(
                'No owner for this association.',
                $data
            );
        }

        if (!isset($data['association_type'])) {
            throw new InvalidItemException(
                'Missing association_type for this association.',
                $data
            );
        }

        $associationTypeRepo = $this->doctrine->getManagerForClass($this->associationTypeClass)
            ->getRepository($this->associationTypeClass);
        $associationType = $associationTypeRepo->findOneByIdentifier($data['association_type']);
        if (!$associationType) {
            throw new InvalidItemException(
                'The association type %association_type% does not exist',
                $data,
                ['%association_type%' => $data['association_type']]
            );
        }

        $productRepository = $this->doctrine->getManagerForClass($this->productClass)
            ->getRepository($this->productClass);
        $product = $productRepository->findOneByIdentifier($data['owner']);
        if (!$product) {
            throw new InvalidItemException(
                'No product with identifier %identifier%',
                $data,
                ['%identifier%' => $data['owner']]
            );
        }

        $association = $product->getAssociationForTypeCode($data['association_type']);

        return $association;
    }
}
