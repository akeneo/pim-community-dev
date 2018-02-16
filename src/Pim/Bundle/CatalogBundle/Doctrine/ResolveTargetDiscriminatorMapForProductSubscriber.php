<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use Pim\Component\Catalog\Model\AbstractProduct;

/**
 * Resolve the discriminator map of the AbstractProduct class
 * with the concrete Product and VariantProduct entities.
 * This allows the override of the Product and VariantProduct classes.
 *
 * It is important to use "ClassMetadata::setDiscriminatorMap()" method, as this
 * method will also set the "ClassMetadata::subClasses" field, mandatory to have
 * a complete mapping with all the fields of both Product and VariantProduct.
 *
 * @author    Julien Janvier (julien.janvier@akeneo.com)
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ResolveTargetDiscriminatorMapForProductSubscriber implements EventSubscriber
{
    /** @var string */
    private $productClass;

    /** @var string */
    private $variantProductClass;

    /**
     * @param string $productClass
     * @param string $variantProductClass
     */
    public function __construct(string $productClass, string $variantProductClass)
    {
        $this->productClass = $productClass;
        $this->variantProductClass = $variantProductClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            'loadClassMetadata',
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $args
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $classMetadata = $args->getClassMetadata();
        $className = $classMetadata->getName();

        if (AbstractProduct::class === $className) {
            $classMetadata->discriminatorMap = [];
            $classMetadata->setDiscriminatorMap([
                'product' => $this->productClass,
                'variant_product' => $this->variantProductClass,
            ]);
        }
    }
}
