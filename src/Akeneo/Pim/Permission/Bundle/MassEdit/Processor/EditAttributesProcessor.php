<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Akeneo\Pim\Permission\Bundle\MassEdit\Processor;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditAttributesProcessor as BaseProcessor;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * It edits an entity with values but check if the user has right to mass edit the product (if he is the owner).
 *
 * @author Samir Boulil <samir.boulil@akeneo.com>
 */
class EditAttributesProcessor extends BaseProcessor
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var UserManager */
    protected $userManager;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param ValidatorInterface                    $productValidator
     * @param ValidatorInterface                    $productModelValidator
     * @param ObjectUpdaterInterface                $productUpdater
     * @param ObjectUpdaterInterface                $productModelUpdater
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param CheckAttributeEditable                $checkAttributeEditable
     * @param UserManager                           $userManager
     * @param TokenStorageInterface                 $tokenStorage
     * @param AuthorizationCheckerInterface         $authorizationChecker
     */
    public function __construct(
        ValidatorInterface $productValidator,
        ValidatorInterface $productModelValidator,
        ObjectUpdaterInterface $productUpdater,
        ObjectUpdaterInterface $productModelUpdater,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        CheckAttributeEditable $checkAttributeEditable,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct(
            $productValidator,
            $productModelValidator,
            $productUpdater,
            $productModelUpdater,
            $attributeRepository,
            $checkAttributeEditable
        );

        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     *
     * We override parent to initialize the security context
     */
    public function process($product)
    {
        $this->initSecurityContext($this->stepExecution);

        return BaseProcessor::process($product);
    }

    /**
     * Initialize the SecurityContext from the given $stepExecution
     *
     * @param StepExecution $stepExecution
     */
    protected function initSecurityContext(StepExecution $stepExecution)
    {
        $username = $stepExecution->getJobExecution()->getUser();
        $user = $this->userManager->findUserByUsername($username);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
    }

    /**
     * {@inheritdoc}
     */
    protected function isEntityEditable(EntityWithFamilyInterface $entity): bool
    {
        if (!$this->authorizationChecker->isGranted(Attributes::OWN, $entity)
            && !$this->authorizationChecker->isGranted(Attributes::EDIT, $entity)
        ) {
            return false;
        }

        return parent::isEntityEditable($entity);
    }
}
