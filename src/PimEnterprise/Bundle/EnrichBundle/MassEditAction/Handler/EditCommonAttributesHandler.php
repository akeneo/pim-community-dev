<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Handler;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Handler\EditCommonAttributesHandler as BaseHandler;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributesHandler extends BaseHandler
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var UserManager */
    protected $userManager;

    function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductUpdaterInterface $productUpdater,
        BulkSaverInterface $productSaver,
        ObjectDetacherInterface $objectDetacher,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator,
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository,
        UserManager $userManager,
        SecurityContextInterface $securityContext
    ) {
        parent::__construct(
            $pqbFactory,
            $productUpdater,
            $productSaver,
            $objectDetacher,
            $paginatorFactory,
            $validator,
            $massActionRepository,
            $attributeRepository
        );

        $this->securityContext = $securityContext;
        $this->userManager = $userManager;
    }

    /**
     * {@inheritdoc}
     *
     * We override parent to initialize the security context
     */
    public function execute(array $configuration)
    {
        $this->initSecurityContext($this->stepExecution);

        parent::execute($configuration);
    }

    /**
     * TODO: Extract this when refactoring handlers.
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
     * {@inheritdoc}
     *
     * Check if user have own right on updated products, mark them valid and
     * create a warning for other products
     *
     */
    protected function getValidProducts($updatedProducts)
    {
        $ownedProducts = [];

        foreach ($updatedProducts as $index => $product) {
            $isAuthorized = $this->securityContext->isGranted(Attributes::OWN, $product);

            if ($isAuthorized) {
                $ownedProducts[] = $product;
            } else {
                $this->stepExecution->addWarning(
                    $this->getName(),
                    'pim_enrich.mass_edit_action.edit_common_attributes.message.error',
                    [],
                    $product
                );
                $this->stepExecution->incrementSummaryInfo('skipped_products');
            }
        }

        return parent::getValidProducts($ownedProducts);
    }
}
