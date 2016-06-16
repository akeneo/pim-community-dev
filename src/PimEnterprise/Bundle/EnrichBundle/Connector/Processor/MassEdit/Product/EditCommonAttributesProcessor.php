<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

namespace PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditCommonAttributesProcessor as BaseProcessor;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * It edits a product value but check if the user has right to mass edit the product (if he is the owner).
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class EditCommonAttributesProcessor extends BaseProcessor
{
    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var UserManager */
    protected $userManager;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param ValidatorInterface            $validator
     * @param ProductRepositoryInterface    $productRepository
     * @param ObjectUpdaterInterface        $productUpdater
     * @param ObjectDetacherInterface       $productDetacher
     * @param UserManager                   $userManager
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        ValidatorInterface $validator,
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        ObjectDetacherInterface $productDetacher,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        parent::__construct(
            $validator,
            $productRepository,
            $productUpdater,
            $productDetacher
        );

        $this->tokenStorage         = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->userManager          = $userManager;
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
    protected function isProductEditable(ProductInterface $product)
    {
        if (!$this->authorizationChecker->isGranted(Attributes::OWN, $product)
            && !$this->authorizationChecker->isGranted(Attributes::EDIT, $product)
        ) {
            return false;
        }

        return parent::isProductEditable($product);
    }
}
