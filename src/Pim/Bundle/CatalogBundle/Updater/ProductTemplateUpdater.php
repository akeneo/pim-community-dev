<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;

/**
 * Update many products at a time from the product template values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateUpdater implements ProductTemplateUpdaterInterface
{
    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /**
     * @param ProductUpdaterInterface $productUpdater
     */
    public function __construct(ProductUpdaterInterface $productUpdater)
    {
        $this->productUpdater = $productUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ProductTemplateInterface $template, array $products)
    {
        $updates = $template->getValuesData();
        foreach ($updates as $attributeCode => $values) {
            foreach ($values as $value) {
                $this->productUpdater->setValue(
                    $products,
                    $attributeCode,
                    $value['value'],
                    $value['locale'],
                    $value['scope']
                );
            }
        }
    }
}
