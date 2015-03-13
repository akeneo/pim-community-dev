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
            foreach ($values as $data) {
                $this->updateProducts($products, $attributeCode, $data);
            }
        }
    }

    /**
     * @param array  $products
     * @param string $attributeCode
     * @param mixed  $data
     */
    protected function updateProducts(array $products, $attributeCode, $data)
    {
        foreach ($products as $product) {
            $this->productUpdater->setData(
                $product,
                $attributeCode,
                $data['value'],
                ['locale' => $data['locale'], 'scope' => $data['scope']]
            );
        }
    }
}
