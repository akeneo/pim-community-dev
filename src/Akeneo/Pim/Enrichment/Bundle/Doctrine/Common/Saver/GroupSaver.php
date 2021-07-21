<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionContext;
use Akeneo\Tool\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Group saver, contains custom logic for variant group products saving
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var BulkSaverInterface */
    protected $productSaver;

    /** @var VersionContext */
    protected $versionContext;

    /** @var SavingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $productQueryBuilderFactory;

    /** @var BulkObjectDetacherInterface */
    protected $detacher;

    /** @var string */
    protected $productClassName;

    /**
     * @param ObjectManager                       $objectManager
     * @param BulkSaverInterface                  $productSaver
     * @param VersionContext                      $versionContext
     * @param SavingOptionsResolverInterface      $optionsResolver
     * @param EventDispatcherInterface            $eventDispatcher
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param BulkObjectDetacherInterface         $detacher
     * @param string                              $productClassName
     */
    public function __construct(
        ObjectManager $objectManager,
        BulkSaverInterface $productSaver,
        VersionContext $versionContext,
        SavingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        BulkObjectDetacherInterface $detacher,
        $productClassName
    ) {
        $this->objectManager = $objectManager;
        $this->productSaver = $productSaver;
        $this->versionContext = $versionContext;
        $this->optionsResolver = $optionsResolver;
        $this->eventDispatcher = $eventDispatcher;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->detacher = $detacher;
        $this->productClassName = $productClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function save($group, array $options = [])
    {
        $this->validateGroup($group);

        $options = $this->optionsResolver->resolveSaveOptions($options);

        $this->eventDispatcher->dispatch(new GenericEvent($group, $options), StorageEvents::PRE_SAVE);

        $this->persistGroupAndSaveAssociatedProducts($group);

        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(new GenericEvent($group), StorageEvents::POST_SAVE);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $groups, array $options = [])
    {
        if (empty($groups)) {
            return;
        }

        $options = $this->optionsResolver->resolveSaveAllOptions($options);

        $this->eventDispatcher->dispatch(new GenericEvent($groups, $options), StorageEvents::PRE_SAVE_ALL);

        foreach ($groups as $group) {
            $this->validateGroup($group);

            $this->eventDispatcher->dispatch(new GenericEvent($group, $options), StorageEvents::PRE_SAVE);

            $this->persistGroupAndSaveAssociatedProducts($group);
        }

        $this->objectManager->flush();

        foreach ($groups as $group) {
            $this->eventDispatcher->dispatch(new GenericEvent($group, $options), StorageEvents::POST_SAVE);
        }

        $this->eventDispatcher->dispatch(new GenericEvent($groups, $options), StorageEvents::POST_SAVE_ALL);
    }

    /**
     * Save associated products updated by the variant group update
     * Only removed and added products will be saved.
     *
     * @todo Find a better solution than a database query to determine what are the products that have been removed or added
     *       (it will certainly cause a BC-break)
     */
    protected function saveAssociatedProducts(GroupInterface $group)
    {
        $productsToUpdate = [];
        foreach ($group->getProducts() as $product) {
            $productsToUpdate[$product->getId()] = $product;
        }

        if (null !== $group->getId()) {
            $pqb = $this->productQueryBuilderFactory->create();
            $pqb->addFilter('groups', Operators::IN_LIST, [$group->getCode()]);
            $oldProducts = $pqb->execute();
            foreach ($oldProducts as $oldProduct) {
                if (!isset($productsToUpdate[$oldProduct->getId()])) {
                    $oldProduct->removeGroup($group);
                    $productsToUpdate[$oldProduct->getId()] = $oldProduct;
                } else {
                    unset($productsToUpdate[$oldProduct->getId()]);
                }
            }
        }

        if (!empty($productsToUpdate)) {
            $this->productSaver->saveAll(array_values($productsToUpdate));
        }
    }

    protected function validateGroup($group)
    {
        if (!$group instanceof GroupInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Akeneo\Pim\Enrichment\Component\Product\Model\GroupInterface", "%s" provided.',
                    ClassUtils::getClass($group)
                )
            );
        }
    }

    protected function persistGroupAndSaveAssociatedProducts(GroupInterface $group)
    {
        $context = $this->productClassName;
        $this->versionContext->addContextInfo(
            sprintf('Comes from variant group %s', $group->getCode()),
            $context
        );
        $this->objectManager->persist($group);

        $this->saveAssociatedProducts($group);

        $this->versionContext->unsetContextInfo($context);
    }
}
