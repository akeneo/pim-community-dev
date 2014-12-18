<?php

namespace Pim\Bundle\CatalogBundle\Saver;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Resource\Model\BulkSaverInterface;
use Pim\Component\Resource\Model\SaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;

/**
 * Product saver, contains custom logic for product saving
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /**
     * @param ObjectManager       $om
     * @param CompletenessManager $completenessManager
     */
    public function __construct(ObjectManager $om, CompletenessManager $completenessManager)
    {
        $this->objectManager       = $om;
        $this->completenessManager = $completenessManager;
    }

    /**
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

        $options = array_merge(
            [
                'recalculate' => true,
                'flush' => true,
                'schedule' => true,
            ],
            $options
        );

        $this->objectManager->persist($product);

        if (true === $options['schedule'] || true === $options['recalculate']) {
            $this->completenessManager->schedule($product);
        }

        if (true === $options['recalculate'] || true === $options['flush']) {
            $this->objectManager->flush();
        }

        if (true === $options['recalculate']) {
            $this->completenessManager->generateMissingForProduct($product);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $products, array $options = [])
    {
        if (empty($products)) {
            return;
        }

        // TODO : we could detach objects here, based on a saveAll option + detacher !

        $allOptions = array_merge(
            [
                'recalculate' => false,
                'flush' => true,
                'schedule' => true,
            ],
            $options
        );
        $itemOptions = $allOptions;
        $itemOptions['flush'] = false;

        foreach ($products as $product) {
            $this->save($product, $itemOptions);
        }

        if (true === $allOptions['flush']) {
            $this->objectManager->flush();
        }
    }
}
