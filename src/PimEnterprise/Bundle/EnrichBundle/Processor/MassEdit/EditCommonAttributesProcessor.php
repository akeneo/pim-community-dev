<?php

namespace PimEnterprise\Bundle\EnrichBundle\Processor\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\EnrichBundle\Processor\MassEdit\EditCommonAttributesProcessor as BaseProcessor;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
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
        parent::__construct(
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
        if ($this->hasRight($product)) {
            return parent::process($product);
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
        $this->securityContext->setToken($token);
    }

    /**
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function hasRight(ProductInterface $product)
    {
        $isAuthorized = $this->securityContext->isGranted(Attributes::OWN, $product);

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
