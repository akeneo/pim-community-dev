<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Handler;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnpublishProductHandler extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /** @var PublishedProductManager */
    protected $manager;

    /** @var PaginatorFactoryInterface */
    protected $paginatorFactory;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $publishedPqbFactory;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var UserManager */
    protected $userManager;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * Constructor.
     *
     * @param ProductQueryBuilderFactoryInterface $publishedPqbFactory
     * @param PublishedProductManager             $manager
     * @param PaginatorFactoryInterface           $paginatorFactory
     * @param ObjectDetacherInterface             $objectDetacher
     * @param UserManager                         $userManager
     * @param SecurityContextInterface            $securityContext
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $publishedPqbFactory,
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ObjectDetacherInterface $objectDetacher,
        UserManager $userManager,
        SecurityContextInterface $securityContext
    ) {
        $this->manager             = $manager;
        $this->paginatorFactory    = $paginatorFactory;
        $this->publishedPqbFactory = $publishedPqbFactory;
        $this->userManager         = $userManager;
        $this->securityContext     = $securityContext;
        $this->objectDetacher      = $objectDetacher;
    }

    /**
     * @param array $configuration
     */
    public function execute(array $configuration)
    {
        $this->initSecurityContext($this->stepExecution);

        $cursor = $this->getPublishedProductsCursor($configuration['filters']);
        $paginator = $this->paginatorFactory->createPaginator($cursor);

        foreach ($paginator as $productsPage) {
            $invalidProducts = [];
            foreach ($productsPage as $index => $product) {
                $isAuthorized = $this->securityContext->isGranted(Attributes::OWN, $product);

                if ($isAuthorized) {
                    $this->stepExecution->incrementSummaryInfo('mass_unpublished');
                } else {
                    $invalidProducts[$index] = $product;
                    $this->stepExecution->incrementSummaryInfo('skipped_products');
                    $this->stepExecution->addWarning(
                        $this->getName(),
                        'pim_enrich.mass_edit_action.unpublish.message.error',
                        [],
                        $product
                    );
                }
            }

            $productsPage = array_diff_key($productsPage, $invalidProducts);
            $this->detachProducts($invalidProducts);
            $this->manager->unpublishAll($productsPage);
            $this->detachProducts($productsPage);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
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
     * @param array $productsPage
     */
    protected function detachProducts(array $productsPage)
    {
        foreach ($productsPage as $product) {
            $this->objectDetacher->detach($product);
        }
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    protected function getPublishedProductQueryBuilder()
    {
        return $this->publishedPqbFactory->create();
    }

    /**
     * @param array $filters
     *
     * @return \Akeneo\Component\StorageUtils\Cursor\CursorInterface
     */
    protected function getPublishedProductsCursor(array $filters)
    {
        $productQueryBuilder = $this->getPublishedProductQueryBuilder();

        $resolver = new OptionsResolver();
        $resolver->setRequired(['field', 'operator', 'value']);
        $resolver->setOptional(['locale', 'scope']);
        $resolver->setDefaults(['locale' => null, 'scope' => null]);

        foreach ($filters as $filter) {
            $filter = $resolver->resolve($filter);
            $context = ['locale' => $filter['locale'], 'scope' => $filter['scope']];
            $productQueryBuilder->addFilter($filter['field'], $filter['operator'], $filter['value'], $context);
        }

        return $productQueryBuilder->execute();
    }
}
