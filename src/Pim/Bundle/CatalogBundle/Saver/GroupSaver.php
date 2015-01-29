<?php

namespace Pim\Bundle\CatalogBundle\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateApplierInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateMediaManager;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;

/**
 * Group saver, contains custom logic for variant group products saving
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupSaver implements SaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var BulkSaverInterface */
    protected $productSaver;

    /** @var ProductTemplateMediaManager */
    protected $templateMediaManager;

    /** @var ProductTemplateApplierInterface */
    protected $productTemplateApplier;

    /**
     * @param ObjectManager                   $objectManager
     * @param BulkSaverInterface              $productSaver
     * @param ProductTemplateMediaManager     $templateMediaManager
     * @param ProductTemplateApplierInterface $productTemplateApplier
     */
    public function __construct(
        ObjectManager $objectManager,
        BulkSaverInterface $productSaver,
        ProductTemplateMediaManager $templateMediaManager,
        ProductTemplateApplierInterface $productTemplateApplier
    ) {
        $this->objectManager          = $objectManager;
        $this->productSaver           = $productSaver;
        $this->templateMediaManager   = $templateMediaManager;
        $this->productTemplateApplier = $productTemplateApplier;
    }

    /**
     * {@inheritdoc}
     */
    public function save($group, array $options = [])
    {
        /** @var GroupInterface */
        if (!$group instanceof GroupInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\GroupInterface", "%s" provided.',
                    ClassUtils::getClass($group)
                )
            );
        }

        // TODO (JJ) options resolver ?
        $defaultOptions = [
            'flush' => true,
            'copy_values_to_products' => false,
            // TODO (JJ) don't understand this option, why it's not a bool ? or bad naming ? products_to_add ?
            'add_products' => [],
            // TODO (JJ) don't understand this option, why it's not a bool ? or bad naming ? products_to_remove ?
            'remove_products' => []
        ];
        $options = array_merge($defaultOptions, $options);
        $this->objectManager->persist($group);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        if (count($options['add_products']) > 0) {
            $this->addProducts($options['add_products']);
        }

        if (count($options['remove_products']) > 0) {
            $this->removeProducts($options['remove_products']);
        }

        if ($group->getType()->isVariant()) {
            $template = $group->getProductTemplate();
            if (null !== $template) {
                $this->templateMediaManager->handleProductTemplateMedia($template);
            }
        }

        if ($group->getType()->isVariant() && true === $options['copy_values_to_products']) {
            $this->copyVariantGroupValues($group);
        }
    }

    /**
     * TODO (JJ) ProductInterface[]
     *
     * @param array $products
     */
    protected function addProducts(array $products)
    {
        $this->productSaver->saveAll($products, ['recalculate' => false, 'schedule' => false]);
    }

    /**
     * TODO (JJ) ProductInterface[]
     *
     * @param array $products
     */
    protected function removeProducts(array $products)
    {
        $this->productSaver->saveAll($products, ['recalculate' => false, 'schedule' => false]);
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
        $this->productTemplateApplier->apply($template, $products);
    }
}
