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
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Comparator\ChainedComparator;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Draft builder to have modifications on product values
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductDraftBuilder implements ProductDraftBuilderInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ChainedComparator */
    protected $comparator;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param ObjectManager                $objectManager
     * @param NormalizerInterface          $normalizer
     * @param ChainedComparator            $comparator
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        ObjectManager $objectManager,
        NormalizerInterface $normalizer,
        ChainedComparator $comparator,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->objectManager       = $objectManager;
        $this->normalizer          = $normalizer;
        $this->comparator          = $comparator;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function build(ProductInterface $product)
    {
        $newValues = $this->normalizer->normalize($product->getValues(), 'json');
        $originalValues = $this->getOriginalValues($product);
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($newValues));

        $diff = [];
        foreach ($newValues as $code => $new) {
            if (!isset($attributeTypes[$code])) {
                throw new \LogicException(sprintf('Cannot find attribute with code "%s". ', $code));
            }

            foreach ($new as $index => $changes) {
                $diffAttribute = $this->comparator->compare(
                    $attributeTypes[$code],
                    $changes,
                    $this->getOriginalValue($originalValues, $code, $index)

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
    protected function getOriginalValues(ProductInterface $product)
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

        return $this->normalizer->normalize($originalValues, 'json');
    }

    /**
     * @param array  $originalValues
     * @param string $code
     * @param int    $index
     *
     * @return array
     */
    protected function getOriginalValue($originalValues, $code, $index)
    {
        return !isset($originalValues[$code][$index]) ? [] : $originalValues[$code][$index];
    }
}
