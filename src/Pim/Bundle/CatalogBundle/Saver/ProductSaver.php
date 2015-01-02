<?php

namespace Pim\Bundle\CatalogBundle\Saver;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Akeneo\Component\Persistence\BulkSaverInterface;
use Akeneo\Component\Persistence\SaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Product saver, define custom logic and options for product saving
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

        $options = $this->resolveSaveOptions($options);

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

        $allOptions = $this->resolveSaveAllOptions($options);
        $itemOptions = $allOptions;
        $itemOptions['flush'] = false;

        foreach ($products as $product) {
            $this->save($product, $itemOptions);
        }

        if (true === $allOptions['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * Resolve options for a single save
     *
     * @param array $options
     *
     * @return array
     */
    protected function resolveSaveOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(
            [
                'flush' => true,
                'recalculate' => true,
                'schedule' => true
            ]
        );
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * Resolve options for a bulk save
     *
     * @param array $options
     *
     * @return array
     */
    protected function resolveSaveAllOptions(array $options)
    {
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(
            [
                'flush' => true,
                'recalculate' => false,
                'schedule' => true,
            ]
        );
        $options = $resolver->resolve($options);

        return $options;
    }

    /**
     * @return OptionsResolverInterface
     */
    protected function createOptionsResolver()
    {
        $resolver = new OptionsResolver();
        $resolver->setOptional(['flush', 'recalculate', 'schedule']);
        $resolver->setAllowedTypes(
            [
                'flush' => 'bool',
                'recalculate' => 'bool',
                'schedule' => 'bool'
            ]
        );

        return $resolver;
    }
}
