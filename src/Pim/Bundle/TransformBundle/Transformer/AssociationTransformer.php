<?php

namespace Pim\Bundle\TransformBundle\Transformer;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\TransformBundle\Transformer\ColumnInfo\ColumnInfoTransformerInterface;
use Pim\Bundle\TransformBundle\Transformer\Guesser\GuesserInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
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
     * Constructor
     *
     * @param ManagerRegistry                $doctrine
     * @param PropertyAccessorInterface      $propertyAccessor
     * @param GuesserInterface               $guesser
     * @param ColumnInfoTransformerInterface $colInfoTransformer
     * @param string                         $productClass
     */
    public function __construct(
        ManagerRegistry $doctrine,
        PropertyAccessorInterface $propertyAccessor,
        GuesserInterface $guesser,
        ColumnInfoTransformerInterface $colInfoTransformer,
        $productClass
    ) {
        parent::__construct($doctrine, $propertyAccessor, $guesser, $colInfoTransformer);
        $this->productClass = $productClass;
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

        $productRepository = $this->doctrine->getManagerForClass($this->productClass)
            ->getRepository($this->productClass);
        $product = $productRepository->findByReference($data['owner']);
        if (!$product) {
            throw new InvalidItemException(
                'No product with identifier %identifier%',
                $data,
                ['%identifier%' => $data['owner']]
            );
        }

        return $product->getAssociationForTypeCode($data['association_type']);
    }
}
