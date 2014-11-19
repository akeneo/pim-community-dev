<?php

namespace Pim\Bundle\CatalogBundle\Saver;

use Doctrine\Common\Persistence\ManagerRegistry;
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
    protected $om;

    /** @var CompletenessManager */
    protected $completenessManager;

    /**
     * @param ObjectManager       $om
     * @param CompletenessManager $completenessManager
     */
    public function __construct(ObjectManager $om, CompletenessManager $completenessManager)
    {
        $this->om                  = $om;
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

        $this->om->persist($product);

        if (true === $options['schedule'] || true === $options['recalculate']) {
            $this->completenessManager->schedule($product);
        }

        if (true === $options['recalculate'] || true === $options['flush']) {
            $this->om->flush();
        }

        if (true === $options['recalculate']) {
            $this->completenessManager->generateMissingForProduct($product);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $objects, array $options = [])
    {
        if (empty($objects)) {
            return;
        }

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

        foreach ($objects as $object) {
            $this->save($object, $itemOptions);
        }

        if (true === $allOptions['flush']) {
            $this->om->flush();
        }
    }
}
