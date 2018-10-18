<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace Akeneo\Pim\Permission\Bundle\MassEdit\Processor;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\MassEdit\AddAttributeValueProcessor as BaseProcessor;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\CheckAttributeEditable;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyAdderInterface;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * It edits an entity with values but check if the user has right to mass edit the product (if he is the owner).
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class AddAttributeValueProcessor extends BaseProcessor
{
    /** @var UserManager */
    protected $userManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param ValidatorInterface                    $productValidator
     * @param ValidatorInterface                    $productModelValidator
     * @param PropertyAdderInterface                $propertyAdder
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param CheckAttributeEditable                $checkAttributeEditable
     * @param array                                 $supportedTypes
     * @param UserManager                           $userManager
     * @param TokenStorageInterface                 $tokenStorage
     * @param AuthorizationCheckerInterface         $authorizationChecker
     *
     *  @todo merge : remove properties $userManager and $tokenStorage in master branch. They are no longer used.
     */
    public function __construct(
        ValidatorInterface $productValidator,
        ValidatorInterface $productModelValidator,
        PropertyAdderInterface $propertyAdder,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        CheckAttributeEditable $checkAttributeEditable,
        array $supportedTypes,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct(
            $productValidator,
            $productModelValidator,
            $propertyAdder,
            $attributeRepository,
            $checkAttributeEditable,
            $supportedTypes
        );

        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Initialize the SecurityContext from the given $stepExecution
     *
     * @param StepExecution $stepExecution
     *
     * @deprecated will be removed in 3.0
     *
     * @todo merge : remove this method in master branch. It's no longer used
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
