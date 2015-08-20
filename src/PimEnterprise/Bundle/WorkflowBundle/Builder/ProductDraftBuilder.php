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
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Comparator\ComparatorRegistry;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\PimEnterpriseWorkflowBundle;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
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

    /** @var ComparatorRegistry */
    protected $comparatorRegistry;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductDraftFactory */
    protected $factory;

    /** @var ProductDraftRepositoryInterface */
    protected $productDraftRepo;

    /**
     * @param ObjectManager                   $objectManager
     * @param NormalizerInterface             $normalizer
     * @param ComparatorRegistry              $comparatorRegistry
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param ProductDraftFactory             $factory
     * @param ProductDraftRepositoryInterface $productDraftRepo
     */
    public function __construct(
        ObjectManager $objectManager,
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        AttributeRepositoryInterface $attributeRepository,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $productDraftRepo
    ) {
        $this->objectManager          = $objectManager;
        $this->normalizer             = $normalizer;
        $this->comparatorRegistry     = $comparatorRegistry;
        $this->attributeRepository    = $attributeRepository;
        $this->factory                = $factory;
        $this->productDraftRepository = $productDraftRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function build(ProductInterface $product, $username)
    {
        $newValues      = $this->normalizer->normalize($product->getValues(), 'json', ['entity' => 'product']);
        $originalValues = $this->getOriginalValues($product);
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($newValues));

        $diff = [];
        foreach ($newValues as $code => $new) {
            if (!isset($attributeTypes[$code])) {
                throw new \LogicException(sprintf('Cannot find attribute with code "%s".', $code));
            }

            foreach ($new as $index => $changes) {
                $comparator = $this->comparatorRegistry->getAttributeComparator($attributeTypes[$code]);
                $diffAttribute = $comparator->compare(
                    $changes,
                    $this->getOriginalValue($originalValues, $code, $index)
                );

                if (null !== $diffAttribute) {
                    $diff['values'][$code][] = $diffAttribute;
                }
            }
        }

        if (!empty($diff)) {
            $productDraft = $this->getProductDraft($product, $username);
            $productDraft->setChanges($diff);
            $productDraft->setStatus(ProductDraftInterface::IN_PROGRESS);

            return $productDraft;
        }

        return null;
    }

    /**
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return ProductDraftInterface
     */
    protected function getProductDraft(ProductInterface $product, $username)
    {
        if (null === $productDraft = $this->productDraftRepository->findUserProductDraft($product, $username)) {
            $productDraft = $this->factory->createProductDraft($product, $username);
        }

        return $productDraft;
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getOriginalValues(ProductInterface $product)
    {
        if (class_exists(PimEnterpriseWorkflowBundle::DOCTRINE_MONGODB)) {
            $originalProduct = $this->objectManager->find(ClassUtils::getClass($product), $product->getId());
            $this->objectManager->refresh($originalProduct);
            $originalValues = $originalProduct->getValues();
        } else {
            $originalValues = new ArrayCollection();
            foreach ($product->getValues() as $value) {
                if (null !== $value->getId()) {
                    $id = $value->getId();
                    $class = ClassUtils::getClass($value);
                    $this->objectManager->detach($value);

                    $value = $this->objectManager->find($class, $id);
                    $originalValues->add($value);
                }
            }
        }

        return $this->normalizer->normalize($originalValues, 'json', ['entity' => 'product']);
    }

    /**
     * @param array  $originalValues
     * @param string $code
     * @param int    $index
     *
     * @return array
     */
    protected function getOriginalValue(array $originalValues, $code, $index)
    {
        return !isset($originalValues[$code][$index]) ? [] : $originalValues[$code][$index];
    }
}
