<?php

namespace Pim\Bundle\CatalogBundle\Saver;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateManager;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateManagerInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Component\Resource\Model\BulkSaverInterface;
use Pim\Component\Resource\Model\SaverInterface;

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

    /** @var ProductTemplateManagerInterface */
    protected $productTemplateManager;

    /**
     * @param ObjectManager                   $objectManager
     * @param BulkSaverInterface              $productSaver
     * @param ProductTemplateManagerInterface $productTemplateManager
     */
    public function __construct(
        ObjectManager $objectManager,
        BulkSaverInterface $productSaver,
        ProductTemplateManagerInterface $productTemplateManager
    ) {
        $this->objectManager   = $objectManager;
        $this->productSaver    = $productSaver;
        $this->productTemplateManager = $productTemplateManager;
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

        $defaultOptions = [
            'flush' => true,
            'copy_values_to_products' => false,
            'add_products' => [],
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

        if ($group->getType()->isVariant() && true === $options['copy_values_to_products']) {
            $this->copyVariantGroupValues($group);
        }
    }

    /**
     * @param array $products
     */
    protected function addProducts(array $products)
    {
        $this->productSaver->saveAll($products, ['recalculate' => false, 'schedule' => false]);
    }

    /**
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
        $this->productTemplateManager->apply($template, $products);
    }
}
