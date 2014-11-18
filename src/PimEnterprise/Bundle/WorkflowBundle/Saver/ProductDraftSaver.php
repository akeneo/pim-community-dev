<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Saver;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Component\Resource\Model\SaverInterface;
use Pim\Bundle\CatalogBundle\DependencyInjection\PimCatalogExtension;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\ProductDraftEvents;
use PimEnterprise\Bundle\WorkflowBundle\Factory\ProductDraftFactory;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangesCollector;
use PimEnterprise\Bundle\WorkflowBundle\ProductDraft\ChangeSetComputerInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Repository\RuleRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Store product through product drafts
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftSaver implements SaverInterface
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var ProductDraftFactory */
    protected $factory;

    /** @var ProductDraftRepositoryInterface */
    protected $repository;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /** @var ChangesCollector */
    protected $collector;

    /** @var ChangeSetComputerInterface */
    protected $changeSet;

    /** @var string */
    protected $storageDriver;

    /** @var RuleRepositoryInterface */
    protected $ruleRepository;

    /** @var RunnerInterface */
    protected $ruleRunner;

    /**
     * @param ManagerRegistry                 $registry
     * @param CompletenessManager             $completenessManager
     * @param SecurityContextInterface        $securityContext
     * @param ProductDraftFactory             $factory
     * @param ProductDraftRepositoryInterface $repository
     * @param EventDispatcherInterface        $dispatcher
     * @param ChangesCollector                $collector
     * @param ChangeSetComputerInterface      $changeSet
     * @param string                          $storageDriver
     * @param RuleRepositoryInterface         $ruleRepository
     * @param RunnerInterface                 $ruleRunner
     */
    public function __construct(
        ManagerRegistry $registry,
        CompletenessManager $completenessManager,
        SecurityContextInterface $securityContext,
        ProductDraftFactory $factory,
        ProductDraftRepositoryInterface $repository,
        EventDispatcherInterface $dispatcher,
        ChangesCollector $collector,
        ChangeSetComputerInterface $changeSet,
        $storageDriver,
        RuleRepositoryInterface $ruleRepository,
        RunnerInterface $ruleRunner
    ) {
        $this->registry = $registry;
        $this->completenessManager = $completenessManager;
        $this->securityContext = $securityContext;
        $this->factory = $factory;
        $this->repository = $repository;
        $this->dispatcher = $dispatcher;
        $this->collector = $collector;
        $this->changeSet = $changeSet;
        $this->storageDriver = $storageDriver;
        $this->ruleRepository = $ruleRepository;
        $this->ruleRunner = $ruleRunner;
    }

    /**
     * TODO: do not check the context here. ProductDraftSaver should only persist propostions.
     *
     * {@inheritdoc}
     */
    public function save($product, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Bundle\CatalogBundle\Model\ProductInterface, "%s" provided',
                    ClassUtils::getClass($product)
                )
            );
        }

        $options = $this->resolveOptions($options);
        $manager = $this->registry->getManagerForClass(get_class($product));

        if (null === $product->getId()) {
            $isOwner = true;
        } else {
            try {
                $isOwner = $this->securityContext->isGranted(Attributes::OWN, $product);
            } catch (AuthenticationCredentialsNotFoundException $e) {
                // We are probably on a CLI context
                $isOwner = true;
            }
        }

        if ($isOwner || $options['bypass_product_draft'] || !$manager->contains($product)) {
            $this->persistProduct($manager, $product, $options);
        } else {
            $this->refreshProductValues($manager, $product);
            $this->persistProductDraft($manager, $product);
        }
    }

    /**
     * Persist the product
     *
     * @param ObjectManager    $manager
     * @param ProductInterface $product
     * @param array            $options
     */
    protected function persistProduct(ObjectManager $manager, ProductInterface $product, array $options)
    {
        // TODO : remove the flush case, once the saveAll will be implemented
        $manager->persist($product);

        if (true === $options['schedule'] || true === $options['recalculate']) {
            $this->completenessManager->schedule($product);
        }

        if (true === $options['recalculate'] || true === $options['flush']) {
            $manager->flush();
        }

        if (true === $options['recalculate']) {
            $this->completenessManager->generateMissingForProduct($product);
        }

        if (true === $options['execute_rules']) {
            $this->applyAllRules($manager, $product);
        }
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'recalculate' => true,
                'flush' => true,
                'schedule' => true,
                'execute_rules' => true,
                'bypass_product_draft' => false
            ]
        );
        $resolver->setAllowedTypes(
            [
                'recalculate' => 'bool',
                'flush' => 'bool',
                'schedule' => 'bool',
                'execute_rules' => 'bool',
                'bypass_product_draft' => 'bool'
            ]
        );
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * Apply the rules on the product
     *
     * @param ObjectManager    $manager
     * @param ProductInterface $product
     */
    protected function applyAllRules(ObjectManager $manager, ProductInterface $product)
    {
        $rules = $this->ruleRepository->findAllOrderedByPriority();
        foreach ($rules as $rule) {
            $this->ruleRunner->run($rule, ['selected_products' => [$product->getId()]]);
            $manager->flush();
        }
    }

    /**
     * Persist a product draft of the product
     *
     * @param ObjectManager    $manager
     * @param ProductInterface $product
     *
     * @return null
     *
     * @throws \LogicException
     */
    protected function persistProductDraft(ObjectManager $manager, ProductInterface $product)
    {
        if (null === $submittedData = $this->collector->getData()) {
            throw new \LogicException('No product data were collected');
        }

        $username = $this->getUser()->getUsername();
        if (null === $productDraft = $this->repository->findUserProductDraft($product, $username)) {
            $productDraft = $this->factory->createProductDraft($product, $username);
            $manager->persist($productDraft);
        }

        $event = $this->dispatcher->dispatch(
            ProductDraftEvents::PRE_UPDATE,
            new ProductDraftEvent(
                $productDraft,
                $this->changeSet->compute($product, $submittedData)
            )
        );
        $changes = $event->getChanges();

        if (empty($changes)) {
            $manager->remove($productDraft);

            return $manager->flush();
        }

        $productDraft->setChanges($changes);

        $manager->flush();
    }

    /**
     * Get user from the security context
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     *
     * @throws \LogicException
     */
    protected function getUser()
    {
        if (null === $token = $this->securityContext->getToken()) {
            throw new \LogicException('No user logged in');
        }

        if (!is_object($user = $token->getUser())) {
            throw new \LogicException('No user logged in');
        }

        return $user;
    }

    /**
     * Refresh the values of the product to not have the changes made by binding the request to the form
     * This is hackish, but no elegant solution has been found
     *
     * @param ObjectManager    $manager
     * @param ProductInterface $product
     */
    protected function refreshProductValues(ObjectManager $manager, ProductInterface $product)
    {
        if (PimCatalogExtension::DOCTRINE_ORM === $this->storageDriver) {
            foreach ($product->getValues() as $value) {
                if (true === $manager->contains($value)) {
                    $manager->refresh($value);
                    foreach ($value->getPrices() as $price) {
                        if (true === $manager->contains($price)) {
                            $manager->refresh($price);
                        }
                    }
                } else {
                    $value->setData(null);
                }
            }
        } else {
            // because of Mongo (values are embedded) we'll have to refresh the whole product instead
            // of refreshing only the values
            // so we'll have to store all other data that could have changed

            $enabled = $product->isEnabled();
            $categories = $product->getCategories();
            $associations = $product->getAssociations();

            $manager->refresh($product);

            $product->setEnabled($enabled);
            $product->getCategories()->clear();
            foreach ($categories as $category) {
                $product->addCategory($category);
            }
            $product->setAssociations($associations->toArray());
        }
    }
}
