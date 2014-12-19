<?php


namespace Pim\Bundle\CatalogBundle\Saver;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
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

    /**
     * @param ObjectManager      $objectManager
     * @param BulkSaverInterface $productSaver
     */
    public function __construct(ObjectManager $objectManager, BulkSaverInterface $productSaver)
    {
        $this->objectManager = $objectManager;
        $this->productSaver  = $productSaver;
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
            'apply_template' => false,
            'append_products' => [],
            'remove_products' => []
        ];
        $options = array_merge($defaultOptions, $options);
        $this->objectManager->persist($group);
        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        if (count($options['append_products']) > 0) {
            $this->addProducts($options['append_products']);
        }

        if (count($options['remove_products']) > 0) {
            $this->removeProducts($options['remove_products']);
        }

        if ($group->getType()->isVariant() && true === $options['apply_template']) {
            $this->applyProductTemplate();
        }
    }

    /**
     * @param array $products
     */
    protected function addProducts(array $products)
    {
        $this->productManager->saveAll($products, ['recalculate' => false, 'schedule' => false]);
    }

    /**
     * @param array $products
     */
    protected function removeProducts(array $products)
    {
        $this->productManager->saveAll($products, ['recalculate' => false, 'schedule' => false]);
    }

    /**
     * Apply the product template values on any products of the variant group
     */
    protected function applyProductTemplate()
    {
        // TODO : apply the template on products
        // - update
        // - validate
        // - save
    }
}