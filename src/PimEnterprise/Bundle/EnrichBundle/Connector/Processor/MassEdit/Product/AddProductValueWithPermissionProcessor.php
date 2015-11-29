<?php

namespace PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Updater\PropertyAdderInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\AddProductValueProcessor as BaseProcessor;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * It adds a product value but check if the user has right to mass edit the product (if he is the owner).
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductValueWithPermissionProcessor extends BaseProcessor
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var UserManager */
    protected $userManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param PropertyAdderInterface              $propertyAdder
     * @param ValidatorInterface                  $validator
     * @param JobConfigurationRepositoryInterface $jobConfigurationRepo
     * @param UserManager                         $userManager
     * @param AuthorizationCheckerInterface       $authorizationChecker
     * @param TokenStorageInterface               $tokenStorage
     */
    public function __construct(
        PropertyAdderInterface $propertyAdder,
        ValidatorInterface $validator,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        UserManager $userManager,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        BaseProcessor::__construct(
            $propertyAdder,
            $validator,
            $jobConfigurationRepo
        );

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
        $this->initSecurityContext($this->stepExecution);
        if ($this->hasRight($product)) {
            return BaseProcessor::process($product);
        }

        return null;
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
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function hasRight(ProductInterface $product)
    {
        $isAuthorized = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

        if (!$isAuthorized) {
            $this->stepExecution->addWarning(
                $this->getName(),
                'pim_enrich.mass_edit_action.edit_common_attributes.message.error',
                [],
                $product
            );
            $this->stepExecution->incrementSummaryInfo('skipped_products');
        }

        return $isAuthorized;
    }
}
