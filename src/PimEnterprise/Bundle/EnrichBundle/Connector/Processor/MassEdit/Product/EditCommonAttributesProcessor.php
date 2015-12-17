<?php

namespace PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditCommonAttributesProcessor as BaseProcessor;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Pim\Component\Localization\Localizer\LocalizerRegistryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @param ValidatorInterface                  $validator
     * @param AttributeRepositoryInterface        $attributeRepository
     * @param JobConfigurationRepositoryInterface $jobConfigurationRepo
     * @param LocalizerRegistryInterface          $localizerRegistry
     * @param ObjectUpdaterInterface              $productUpdater
     * @param UserManager                         $userManager
     * @param TokenStorageInterface               $tokenStorage
     * @param AuthorizationCheckerInterface       $authorizationChecker
     */
    public function __construct(
        ValidatorInterface $validator,
        AttributeRepositoryInterface $attributeRepository,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        LocalizerRegistryInterface $localizerRegistry,
        ObjectUpdaterInterface $productUpdater,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        BaseProcessor::__construct(
            $validator,
            $attributeRepository,
            $jobConfigurationRepo,
            $localizerRegistry,
            $productUpdater
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
