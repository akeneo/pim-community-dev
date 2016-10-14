<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Processor;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Detacher\ObjectDetacher;
use Akeneo\Component\Batch\Model\StepExecution;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\EnrichBundle\Connector\Processor\AbstractProcessor;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class Processor extends AbstractProcessor
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var UserManager */
    private $userManager;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var ObjectDetacher */
    private $objectDetacher;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var VoterInterface */
    private $attributeVoter;

    /** @var AttributeGroupAccessManager */
    private $attributeGroupAccessManager;

    /** @var CategoryAccessManager */
    private $categoryAccessManager;

    /**
     * @param ObjectDetacher                $objectDetacher
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param ProductRepositoryInterface    $productRepository
     * @param VoterInterface                $attributeVoter
     * @param UserManager                   $userManager
     * @param AttributeGroupAccessManager   $attributeGroupAccessManager
     * @param CategoryAccessManager         $categoryAccessManager
     */
    public function __construct(
        ObjectDetacher $objectDetacher,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductRepositoryInterface $productRepository,
        VoterInterface $attributeVoter,
        UserManager $userManager,
        AttributeGroupAccessManager $attributeGroupAccessManager,
        CategoryAccessManager $categoryAccessManager
    ) {
        $this->objectDetacher = $objectDetacher;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->productRepository = $productRepository;
        $this->attributeVoter = $attributeVoter;
        $this->userManager = $userManager;
        $this->attributeGroupAccessManager = $attributeGroupAccessManager;
        $this->categoryAccessManager = $categoryAccessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process($product)
    {
        $this->initSecurityContext($this->stepExecution);

        return $this->getUserGroups($product);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * Initialize the SecurityContext from the given $stepExecution
     *
     * @param StepExecution $stepExecution
     */
    private function initSecurityContext(StepExecution $stepExecution)
    {
//        $username = $stepExecution->getJobExecution()->getUser();
        $username = 'admin';
        $user = $this->userManager->findUserByUsername($username);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }

    /**
     * {@inheritdoc}
     */
    private function isProductEditable(ProductInterface $product)
    {
        if (!$this->authorizationChecker->isGranted(Attributes::EDIT, $product)) {
            return false;
        }

        return true;
    }

    /**
     * @param ProductInterface $product
     * @param $attributeCode
     *
     * @return bool
     */
    private function isAttributeEditable(ProductInterface $product, $attributeCode)
    {
        if ($this->productRepository->hasAttributeInVariantGroup($product->getId(), $attributeCode)) {
            return false;
        }

        return true;
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getUserGroups($product):array
    {
        if (null === $product->getFamily() || !$this->isProductEditable($product)) {
            $this->objectDetacher->detach($product);
            $this->stepExecution->incrementSummaryInfo('skipped_products');

            return null;
        }

        $categories = $product->getCategories(); //detach categories

        $productUserGroups = [];
        foreach ($categories as $category) {
            $productUserGroups = array_merge(
                $productUserGroups,
                $this->categoryAccessManager->getEditUserGroups($category)
            );

            $this->objectDetacher->detach($category);
        }

        $attributeUserGroups = [];
        $attributesRequirement = $product->getFamily()->getAttributeRequirements();
        /** @var AttributeRequirement $attribute */
        foreach ($attributesRequirement as $attributeRequirement) {
            $attribute = $attributeRequirement->getAttribute();
            if ($this->isAttributeEditable($product, $attribute->getCode())
                && $this->attributeVoter->vote(
                    $this->tokenStorage->getToken(),
                    $attribute,
                    [Attributes::EDIT_ATTRIBUTES]
                ) && !$attribute->getProperty('is_read_only')
            ) {
                $attributeGroup = $attribute->getGroup();
                $attributeUserGroups = array_merge(
                    $attributeUserGroups,
                    $this->attributeGroupAccessManager->getEditUserGroups($attributeGroup)
                );
                $this->objectDetacher->detach($attribute);
            }
        }

        $this->objectDetacher->detach($product);

        foreach ($productUserGroups as $userGroup) {
            if ('All' === $userGroup->getName()) {
                return $attributeUserGroups;
            }
        }

        $results = [];
        foreach ($productUserGroups as $productUserGroup) {
            foreach ($attributeUserGroups as $attributeUserGroup) {
                if ($attributeUserGroup->getName() === $productUserGroup->getName()) {
                    $results[] = $attributeUserGroup;
                }
            }
        }

//        echo memory_get_usage()/1024/1024 . "\n";

        return $results;
    }
}
