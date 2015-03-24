<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Copies the category field
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryFieldCopier extends AbstractFieldCopier
{
    /**
     * {@inheritdoc}
     */
    public function copyFieldData(
        ProductInterface $fromProduct,
        ProductInterface $toProduct,
        $fromField,
        $toField,
        array $options = []
    ) {
        $categoriesToCopy   = $fromProduct->getCategories();
        $categoriesToRemove = $toProduct->getCategories();

        foreach ($categoriesToRemove as $categoryToRemove) {
            $toProduct->removeCategory($categoryToRemove);
        }

        foreach ($categoriesToCopy as $categoryToCopy) {
            $toProduct->addCategory($categoryToCopy);
        }
    }
}
