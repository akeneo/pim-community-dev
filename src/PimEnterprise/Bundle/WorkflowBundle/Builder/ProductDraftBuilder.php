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
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
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

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var ProductDraftFactory */
    protected $factory;

    /** @var ProductDraftRepositoryInterface */
    protected $repository;

    /**
     * @param ObjectManager                   $objectManager
     * @param NormalizerInterface             $normalizer
     * @param ComparatorRegistry              $comparatorRegistry
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param SecurityContextInterface        $securityContext
     * @param ProductDraftFactory             $factory
     * @param ProductDraftRepositoryInterface $productDraftRepository
     */
    public function __construct(
        ObjectManager $objectManager,
        NormalizerInterface $normalizer,
        ComparatorRegistry $comparatorRegistry,
        AttributeRepositoryInterface $attributeRepository,
        SecurityContextInterface $securityContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $productDraftRepository
    ) {
        $this->objectManager          = $objectManager;
        $this->normalizer             = $normalizer;
        $this->comparatorRegistry     = $comparatorRegistry;
        $this->attributeRepository    = $attributeRepository;
        $this->securityContext        = $securityContext;
        $this->factory                = $factory;
        $this->productDraftRepository = $productDraftRepository;
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
            $productDraft = $this->getProductDraft($product);
            $productDraft->setChanges($diff);

            return $productDraft;
        }
    }

    /**
     * @param ProductInterface $product
     *
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft
     */
    protected function getProductDraft(ProductInterface $product)
    {
        $username = $this->getUser()->getUsername();
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
     * Get user from the security context
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     *
     * @throws \LogicException
     */
    protected function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            throw new \LogicException('No user logged in');
        }

        if (!is_object($user = $token->getUser())) {
            throw new \LogicException('No user logged in');
        }

        return $user;
    }
}
