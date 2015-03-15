<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\Denormalization;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Product import processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationProcessor extends AbstractProcessor
{
    /** @var string */
    protected $format;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository       repository to search the object in
     * @param DenormalizerInterface                 $denormalizer     denormalizer used to transform array to object
     * @param ValidatorInterface                    $validator        validator of the object
     * @param ObjectDetacherInterface               $detacher         detacher to remove it from UOW when skip
     * @param FieldNameBuilder                      $fieldNameBuilder product manager
     * @param string                                $class            class of the object to instanciate in case if need
     * @param string                                $format           format use to denormalize
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher,
        FieldNameBuilder $fieldNameBuilder,
        $class,
        $productClass,
        $format
    ) {
        parent::__construct($repository, $denormalizer, $validator, $detacher, $class);

        $this->fieldNameBuilder = $fieldNameBuilder;
        $this->format           = $format;
        $this->productClass     = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $identifier = $item['product'][$this->repository->getIdentifierProperties()[0]];
        $product    = $this->findProduct($identifier);
        if (null === $product) {
            throw new \LogicException(sprintf('No product with identifier "%s" has been found', $identifier));
        }

        foreach ($product->getAssociations() as $association) {
            foreach ($association->getGroups() as $group) {
                $association->removeGroup($group);
            }

            foreach ($association->getProducts() as $prod) {
                $association->removeProduct($prod);
            }
        }

        $associations = [];
        foreach ($item['associations'] as $itemAssociation) {
            $association = $product->getAssociationForTypeCode($itemAssociation['association_type_code']);

            $association = $this->denormalizer->denormalize(
                $itemAssociation['associated_items'],
                $this->class,
                $this->format,
                [
                    'entity'                => $association,
                    'association_type_code' => $itemAssociation['association_type_code'],
                    'part'                  => $itemAssociation['item_type']
                ]
            );

            if (null !== $association) {
                $association->setOwner($product);

                $violations = $this->validator->validate($association);

                if (count($violations) === 0) {
                    $associations[] = $association;
                }
            }
        }

        return $associations;
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface|null
     */
    public function findProduct($identifier)
    {
        $product = $this->repository->findOneByIdentifier($identifier);

        return $product;
    }
}
