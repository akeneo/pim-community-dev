<?php

namespace PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\Product\EditCommonAttributesProcessor as BaseProcessor;
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
     * @param PropertySetterInterface              $propertySetter
     * @param ValidatorInterface                   $validator
     * @param ProductMassActionRepositoryInterface $massActionRepository
     * @param AttributeRepositoryInterface         $attributeRepository
     * @param JobConfigurationRepositoryInterface  $jobConfigurationRepo
     * @param LocalizerRegistryInterface           $localizerRegistry
     * @param UserManager                          $userManager
     * @param TokenStorageInterface                $tokenStorage
     * @param AuthorizationCheckerInterface        $authorizationChecker
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        ValidatorInterface $validator,
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        LocalizerRegistryInterface $localizerRegistry,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        BaseProcessor::__construct(
            $propertySetter,
            $validator,
            $massActionRepository,
            $attributeRepository,
            $jobConfigurationRepo,
            $localizerRegistry
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
