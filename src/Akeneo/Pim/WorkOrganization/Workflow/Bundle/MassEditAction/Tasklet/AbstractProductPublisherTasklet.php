<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\MassEditAction\Tasklet;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Connector\Step\TaskletInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Basic implementation of a product publisher/unpublisher tasklet
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
abstract class AbstractProductPublisherTasklet implements TaskletInterface
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
        $this->manager = $manager;
        $this->paginatorFactory = $paginatorFactory;
        $this->validator = $validator;
        $this->objectDetacher = $objectDetacher;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
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
     * @param array $filters
     *
     * @return ProductQueryBuilderInterface
     */
    abstract protected function getProductQueryBuilder(array $filters = []);

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
