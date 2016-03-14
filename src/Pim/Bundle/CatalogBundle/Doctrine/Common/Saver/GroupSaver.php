<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\VersioningBundle\Manager\VersionContext;
use Pim\Component\Catalog\Manager\ProductTemplateApplierInterface;
use Pim\Component\Catalog\Manager\ProductTemplateMediaManager;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
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

    /** @var ProductTemplateMediaManager */
    protected $templateMediaManager;

    /** @var ProductTemplateApplierInterface */
    protected $productTplApplier;

    /** @var VersionContext */
    protected $versionContext;

    /** @var SavingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $productQueryBuilderFactory;

    /** @var string */
    protected $productClassName;

    /**
     * @param ObjectManager                       $objectManager
     * @param BulkSaverInterface                  $productSaver
     * @param ProductTemplateMediaManager         $templateMediaManager
     * @param ProductTemplateApplierInterface     $productTplApplier
     * @param VersionContext                      $versionContext
     * @param SavingOptionsResolverInterface      $optionsResolver
     * @param EventDispatcherInterface            $eventDispatcher
     * @param ProductQueryBuilderFactoryInterface $productQueryBuilderFactory
     * @param string                              $productClassName
     */
    public function __construct(
        ObjectManager $objectManager,
        BulkSaverInterface $productSaver,
        ProductTemplateMediaManager $templateMediaManager,
        ProductTemplateApplierInterface $productTplApplier,
        VersionContext $versionContext,
        SavingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher,
        ProductQueryBuilderFactoryInterface $productQueryBuilderFactory,
        $productClassName
    ) {
        $this->objectManager              = $objectManager;
        $this->productSaver               = $productSaver;
        $this->templateMediaManager       = $templateMediaManager;
        $this->productTplApplier          = $productTplApplier;
        $this->versionContext             = $versionContext;
        $this->optionsResolver            = $optionsResolver;
        $this->eventDispatcher            = $eventDispatcher;
        $this->productQueryBuilderFactory = $productQueryBuilderFactory;
        $this->productClassName           = $productClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function save($group, array $options = [])
    {
        if (!$group instanceof GroupInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Component\Catalog\Model\GroupInterface", "%s" provided.',
                    ClassUtils::getClass($group)
                )
            );
        }

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($group));

        $options = $this->optionsResolver->resolveSaveOptions($options);

        $context = $this->productClassName;
        $this->versionContext->addContextInfo(
            sprintf('Comes from variant group %s', $group->getCode()),
            $context
        );

        if ($group->getType()->isVariant()) {
            $template = $group->getProductTemplate();
            if (null !== $template) {
                $this->templateMediaManager->handleProductTemplateMedia($template);
            }
        }

        $this->objectManager->persist($group);

        $this->saveAssociatedProducts($group);

        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        if ($group->getType()->isVariant() && true === $options['copy_values_to_products']) {
            $this->copyVariantGroupValues($group);
        }

        $this->versionContext->unsetContextInfo($context);

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($group));
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $groups, array $options = [])
    {
        if (empty($groups)) {
            return;
        }

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($groups));

        $allOptions = $this->optionsResolver->resolveSaveAllOptions($options);
        $itemOptions = $allOptions;
        $itemOptions['flush'] = false;

        foreach ($groups as $group) {
            $this->save($group, $itemOptions);
        }

        if (true === $allOptions['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($groups));
    }

    /**
     * Copy the variant group values on any products belonging in the variant group
     *
     * @param GroupInterface $group
     */
    protected function copyVariantGroupValues(GroupInterface $group)
    {
        $template = $group->getProductTemplate();
        $products = $group->getProducts()->toArray();
        $this->productTplApplier->apply($template, $products);
    }

    /**
     * Save associated products updated by the variant group update
     *
     * @param  GroupInterface $group
     */
    protected function saveAssociatedProducts(GroupInterface $group)
    {
        $productInGroup  = $group->getProducts();
        $productToUpdate = $productInGroup->toArray();

        if (null !== $group->getId()) {
            $pqb = $this->productQueryBuilderFactory->create();
            $pqb->addFilter('groups.id', 'IN', [$group->getId()]);
            $oldProducts = $pqb->execute();

            foreach ($oldProducts as $oldProduct) {
                if (!$productInGroup->contains($oldProduct)) {
                    $oldProduct->removeGroup($group);
                    $productToUpdate[] = $oldProduct;
                }
            }
        }

        if (!empty($productToUpdate)) {
            $this->productSaver->saveAll($productToUpdate);
        }
    }
}
