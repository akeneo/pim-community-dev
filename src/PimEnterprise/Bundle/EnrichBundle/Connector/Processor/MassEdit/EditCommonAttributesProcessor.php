<?php

namespace PimEnterprise\Bundle\EnrichBundle\Connector\Processor\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\EditCommonAttributesProcessor as BaseProcessor;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributesProcessor extends BaseProcessor
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var UserManager */
    protected $userManager;

    /**
     * @param PropertySetterInterface              $propertySetter
     * @param ValidatorInterface                   $validator
     * @param ProductMassActionRepositoryInterface $massActionRepository
     * @param AttributeRepositoryInterface         $attributeRepository
     * @param JobConfigurationRepositoryInterface  $jobConfigurationRepo
     * @param UserManager                          $userManager
     * @param SecurityContextInterface             $securityContext
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        ValidatorInterface $validator,
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        UserManager $userManager,
        SecurityContextInterface $securityContext
    ) {
        \Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\parent::__construct(
            $propertySetter,
            $validator,
            $massActionRepository,
            $attributeRepository,
            $jobConfigurationRepo
        );

        $this->securityContext = $securityContext;
        $this->userManager     = $userManager;
    }

    /**
     * {@inheritdoc}
     *
     * We override parent to initialize the security context
     */
    public function process($product)
    {
        $this->initSecurityContext($this->stepExecution);

        return \Pim\Bundle\EnrichBundle\Connector\Processor\MassEdit\parent::process($product);
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
        $this->securityContext->setToken($token);
    }
}
