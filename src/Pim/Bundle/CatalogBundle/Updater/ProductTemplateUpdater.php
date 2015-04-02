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
        $updates = $this->productTemplateToUpdates($template);

        foreach ($products as $product) {
            // TODO: should the product be validated here ?
            $this->productUpdater->update($product, $updates);
        }
    }

    /**
     * Transforms product template data to a set of applicable updates.
     *
     * @param ProductTemplateInterface $template
     *
     * @return array
     */
    protected function productTemplateToUpdates(ProductTemplateInterface $template)
    {
        $updates = [];

        foreach ($template->getValuesData() as $attributeCode => $values) {
            foreach ($values as $data) {
                $updates[] = [
                    $attributeCode,
                    $data['value'],
                    ['locale' => $data['locale'], 'scope' => $data['scope']]
                ];
            }
        }

        return ['set_data' => $updates];
    }
}
