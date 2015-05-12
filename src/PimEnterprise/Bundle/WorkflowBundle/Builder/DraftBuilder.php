<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Builder;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\UnitOfWork;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Comparator\ChainedComparator;
use PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Draft builder to have modifications on product values
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class DraftBuilder implements BuilderInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var ChainedComparator */
    protected $comparator;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ObjectManager                $objectManager
     * @param SerializerInterface          $serializer
     * @param ComparatorInterface          $comparator
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        ObjectManager $objectManager,
        SerializerInterface $serializer,
        ChainedComparator $comparator,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->objectManager       = $objectManager;
        $this->serializer          = $serializer;
        $this->comparator          = $comparator;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {$inheritdoc}
     */
    public function builder(ProductInterface $product)
    {
        $newValues = $this->serializer->normalize($product->getValues(), 'json');
        $originalValues = $this->getOriginalValues($product);
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($newValues));

        $diff = [];
        foreach ($newValues as $code => $new) {
            if (!isset($attributeTypes[$code])) {
                throw new \LogicException(sprintf('Cannot find attribute with code "%s". ', $code));
            }

            foreach ($new as $i => $changes) {
                $diffAttribute = $this->comparator->compare(
                    $attributeTypes[$code],
                    $changes,
                    $this->getOriginalValue($originalValues, $code, $i)
                );
                if (null !== $diffAttribute) {
                    $diff['values'][$code][] = $diffAttribute;
                }
            }
        }


        return $diff;
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getOriginalValues(ProductInterface $product)
    {
        $uow = $this->objectManager->getUnitOfWork();

        $originalValues = new ArrayCollection();

        foreach ($product->getValues() as $value) {
            if ($uow->getEntityState($value) === UnitOfWork::STATE_MANAGED) {
                $uow->refresh($value);

                if (AbstractAttributeType::BACKEND_TYPE_PRICE === $value->getAttribute()->getBackendType()) {
                    foreach($value->getData() as $price) {
                        $uow->refresh($price);
                    }
                }
                $originalValues->add($value);
            }
        }

        return $this->serializer->normalize($originalValues, 'json');
    }

    /**
     * @param array  $originalValues
     * @param string $code
     * @param int    $i
     *
     * @return array
     */
    protected function getOriginalValue($originalValues, $code, $i)
    {
        return !isset($originalValues[$code][$i]) ? [] : $originalValues[$code][$i];
    }
}
