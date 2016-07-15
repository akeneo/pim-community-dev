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

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\StorageUtils\Updater\PropertyRemoverInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\RemoveProductValueProcessor as BaseProcessor;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Processor to remove product value but check if the user has right to mass edit the product (if he is the owner).
 *
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class RemoveProductValueWithPermissionProcessor extends BaseProcessor
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var UserManager */
    protected $userManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param PropertyRemoverInterface      $propertyRemover
     * @param ValidatorInterface            $validator
     * @param UserManager                   $userManager
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface         $tokenStorage
     */
    public function __construct(
        PropertyRemoverInterface $propertyRemover,
        ValidatorInterface $validator,
        UserManager $userManager,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($propertyRemover, $validator);

        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage         = $tokenStorage;
        $this->userManager          = $userManager;
    }

    /**
     * {@inheritdoc}
     *
     * We override parent to initialize the security context
     */
    public function process($product)
    {
        $username = $this->stepExecution->getJobExecution()->getUser();
        $user = $this->userManager->findUserByUsername($username);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        if ($this->authorizationChecker->isGranted(Attributes::OWN, $product)) {
            return BaseProcessor::process($product);
        }

        $this->stepExecution->addWarning(
            'pim_enrich.mass_edit_action.edit_common_attributes.message.error',
            [],
            new DataInvalidItem($product)
        );
        $this->stepExecution->incrementSummaryInfo('skipped_products');

        return null;
    }
}
