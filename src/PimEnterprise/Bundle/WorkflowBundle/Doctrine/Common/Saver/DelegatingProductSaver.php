<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSavingOptionsResolver;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Builder\ProductDraftBuilderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Delegating product saver, depending on context it delegates to other savers to deal with drafts or working copies
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class DelegatingProductSaver implements SaverInterface, BulkSaverInterface
{
    /** @var SaverInterface */
    protected $workingCopySaver;

    /** @var SaverInterface */
    protected $draftSaver;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var ProductSavingOptionsResolver */
    protected $optionsResolver;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var ProductDraftBuilderInterface */
    protected $productDraftBuilder;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param SaverInterface                $workingCopySaver
     * @param SaverInterface                $draftSaver
     * @param ObjectManager                 $objectManager
     * @param ProductSavingOptionsResolver  $optionsResolver
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param ProductDraftBuilderInterface  $productDraftBuilder
     * @param TokenStorageInterface         $tokenStorage
     */
    public function __construct(
        SaverInterface $workingCopySaver,
        SaverInterface $draftSaver,
        ObjectManager $objectManager,
        ProductSavingOptionsResolver $optionsResolver,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftBuilderInterface $productDraftBuilder,
        TokenStorageInterface $tokenStorage
    ) {
        $this->workingCopySaver     = $workingCopySaver;
        $this->draftSaver           = $draftSaver;
        $this->objectManager        = $objectManager;
        $this->optionsResolver      = $optionsResolver;
        $this->authorizationChecker = $authorizationChecker;
        $this->productDraftBuilder  = $productDraftBuilder;
        $this->tokenStorage         = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AuthenticationCredentialsNotFoundException if not authenticated
     */
    public function save($product, array $options = [])
    {
        $options = $this->optionsResolver->resolveSaveOptions($options);
        $hasPermissions = $this->hasPermissions($product);

        if ($hasPermissions) {
            $this->workingCopySaver->save($product, $options);
        } else {
            $productDraft = $this->productDraftBuilder->build($product, $this->getUsername());
            if (null !== $productDraft) {
                $this->draftSaver->save($productDraft, $options);
            }
        }

        $this->objectManager->refresh($product);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $products, array $options = [])
    {
        if (empty($products)) {
            return;
        }

        $allOptions = $this->optionsResolver->resolveSaveAllOptions($options);
        $itemOptions = $allOptions;
        $itemOptions['flush'] = false;

        foreach ($products as $product) {
            $this->save($product, $itemOptions);
        }

        if (true === $allOptions['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * Returns true if user is owner of the product or if the product does not exist yet or if the token does not exist
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function hasPermissions(ProductInterface $product)
    {
        if (null === $product->getId() || null === $this->tokenStorage->getToken()) {
            $isOwner = true;
        } else {
            $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $product);
        }

        return $isOwner;
    }

    /**
     * @return string
     */
    protected function getUsername()
    {
        return $this->tokenStorage->getToken()->getUser()->getUsername();
    }
}
