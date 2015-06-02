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
use PimEnterprise\Bundle\WorkflowBundle\Comparator\ComparatorRegistry;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
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
    protected $repository;

    /**
     * @param ObjectManager                   $objectManager
     * @param NormalizerInterface             $normalizer
     * @param ComparatorRegistry              $comparatorRegistry
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param ProductDraftFactory             $factory
     * @param ProductDraftRepositoryInterface $productDraftRepository
     */
    public function __construct(
        ObjectManager $objectManager,
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        AttributeRepositoryInterface $attributeRepository,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $productDraftRepository
    ) {
        $this->objectManager          = $objectManager;
        $this->normalizer             = $normalizer;
        $this->comparatorRegistry     = $comparatorRegistry;
        $this->attributeRepository    = $attributeRepository;
        $this->factory                = $factory;
        $this->productDraftRepository = $productDraftRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function build(ProductInterface $product, $username)
    {
        $newValues = $this->normalizer->normalize($product->getValues(), 'json');
        $originalValues = $this->getOriginalValues($product);
        $attributeTypes = $this->attributeRepository->getAttributeTypeByCodes(array_keys($newValues));

        $diff = [];
        foreach ($newValues as $code => $new) {
            if (!isset($attributeTypes[$code])) {
                throw new \LogicException(sprintf('Cannot find attribute with code "%s".', $code));
            }

            foreach ($new as $index => $changes) {
                $comparator = $this->comparatorRegistry->getAttributeComparator($attributeTypes[$code]);
                $diffAttribute = $comparator->getChanges(
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
            $diff = $this->mergeValues($productDraft->getChanges(), $diff);
            $productDraft->setChanges($diff);

            return $productDraft;
        }

        return null;
    }

    /**
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft
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

        return $this->normalizer->normalize($originalValues, 'json');
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

    /**
     * Merge values of old & new draft
     *
     * @param array $oldValues
     * @param array $newValues
     *
     * @return array
     */
    protected function mergeValues(array $oldValues, array $newValues)
    {
        if (!isset($oldValues['values'])) {
            return $newValues;
        }

        $attributeKeys = $this->getAttributeKeys($oldValues);

        $values = $oldValues;
        foreach ($newValues['values'] as $code => $value) {
            foreach ($value as $data) {
                $key = sprintf('%s-%s-%s', $code, $data['scope'], $data['locale']);
                // replace old values by new
                if (isset($attributeKeys[$key])) {
                    $values['values'][$code][$attributeKeys[$key]] = $data;
                } else {
                    $values['values'][$code][] = $data;
                }
            }
        }

        return $values;
    }

    /**
     * Get all attributes information (code, locale and scope) and position in iterator
     *
     * @param array $values
     *
     * @return array
     */
    protected function getAttributeKeys(array $values)
    {
        $keys = [];
        foreach ($values['values'] as $code => $values) {
            foreach ($values as $index => $data) {
                $keys[sprintf('%s-%s-%s', $code, $data['scope'], $data['locale'])] = $index;
            }
        }

        return $keys;
    }
}
