<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Updater\Setter\SetterRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\CopierRegistryInterface;

/**
 * Update many products at a time
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUpdater implements ProductUpdaterInterface
{
    /** @var SetterRegistryInterface */
    protected $setterRegistry;

    /** @var CopierRegistryInterface */
    protected $copierRegistry;

    /**
     * @param SetterRegistryInterface $setterRegistry
     * @param CopierRegistryInterface $copierRegistry
     */
    public function __construct(SetterRegistryInterface $setterRegistry, CopierRegistryInterface $copierRegistry)
    {
        $this->setterRegistry = $setterRegistry;
        $this->copierRegistry = $copierRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(array $products, $field, $data, array $context = [])
    {
        $setter = $this->setterRegistry->get($field);
        $setter->setValue($products, $field, $data, $context);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function copyValue(array $products, $sourceField, $destinationField, array $context = [])
    {
        $copier = $this->copierRegistry->get($sourceField, $destinationField);
        $copier->copyValue($products, $sourceField, $destinationField, $context);

        return $this;
    }
}
