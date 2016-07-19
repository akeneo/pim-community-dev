<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Tasklet;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Basic implementation of a product publisher/unpublisher tasklet
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
abstract class AbstractProductPublisherTasklet extends AbstractConfigurableStepElement implements TaskletInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var PublishedProductManager */
    protected $manager;

    /** @var PaginatorFactoryInterface */
    protected $paginatorFactory;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var UserManager */
    protected $userManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param PublishedProductManager   $manager
     * @param PaginatorFactoryInterface $paginatorFactory
     * @param ValidatorInterface        $validator
     * @param ObjectDetacherInterface   $objectDetacher
     * @param UserManager               $userManager
     * @param TokenStorageInterface     $tokenStorage
     */
    public function __construct(
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->manager          = $manager;
        $this->paginatorFactory = $paginatorFactory;
        $this->validator        = $validator;
        $this->objectDetacher   = $objectDetacher;
        $this->userManager      = $userManager;
        $this->tokenStorage     = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function execute();

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * @param array $filters
     *
     * @return ProductQueryBuilderInterface
     */
    abstract protected function getProductQueryBuilder(array $filters);

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
     * @param array
     */
    protected function detachProducts(array $productsPage)
    {
        foreach ($productsPage as $product) {
            $this->objectDetacher->detach($product);
        }
    }

    /**
     * @param array $filters
     *
     * @return CursorInterface
     */
    protected function getProductsCursor(array $filters)
    {
        $productQueryBuilder = $this->getProductQueryBuilder($filters);

        return $productQueryBuilder->execute();
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param ProductInterface                 $product
     */
    protected function addWarningMessage($violations, $product)
    {
        foreach ($violations as $violation) {
            // TODO re-format the message, property path doesn't exist for class constraint
            // for instance cf VariantGroupAxis
            $invalidValue = $violation->getInvalidValue();
            if (is_object($invalidValue) && method_exists($invalidValue, '__toString')) {
                $invalidValue = (string) $invalidValue;
            } elseif (is_object($invalidValue)) {
                $invalidValue = get_class($invalidValue);
            }
            $errors = sprintf(
                "%s: %s: %s\n",
                $violation->getPropertyPath(),
                $violation->getMessage(),
                $invalidValue
            );
            $this->stepExecution->addWarning($errors, [], new DataInvalidItem($product));
        }
    }
}
