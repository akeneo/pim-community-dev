<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Doctrine\Common\Persistence\Detacher;
use Pim\Bundle\CatalogBundle\Doctrine\Common\Persistence\DetacherInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductTemplateUpdaterInterface;
use Pim\Component\Resource\Model\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateManager implements ProductTemplateManagerInterface
{
    /** @var ProductTemplateUpdaterInterface */
    protected $templateUpdater;

    /** @var ValidatorInterface */
    protected $productValidator;

    /** @var Detacher */
    protected $productDetacher;

    /** @var BulkSaverInterface */
    protected $productSaver;

    /**
     * @param ProductTemplateUpdaterInterface $templateUpdater
     * @param ValidatorInterface              $productValidator
     * @param DetacherInterface               $productDetacher
     * @param BulkSaverInterface              $productSaver
     */
    public function __construct(
        ProductTemplateUpdaterInterface $templateUpdater,
        ValidatorInterface $productValidator,
        DetacherInterface $productDetacher,
        BulkSaverInterface $productSaver
    ) {
        $this->templateUpdater  = $templateUpdater;
        $this->productValidator = $productValidator;
        $this->productDetacher  = $productDetacher;
        $this->productSaver     = $productSaver;
    }

    public function apply(ProductTemplateInterface $template, array $products)
    {
        // TODO dispatch events ?
        $this->templateUpdater->update($template, $products);

        $results = $this->validateProducts($products);
        $validProducts = $results['products'];
        $violations    = $results['violations'];

        // TODO update the versioning context, the update come from variant group !
        $this->productSaver->saveAll($validProducts);

        return $violations;
    }

    /**
     * @param ProductInterface[] $products
     *
     * @return array ['products' => ProductInterface[], 'violations' => []]
     */
    protected function validateProducts(array $products)
    {
        $validProducts = $products;
        $productViolations = [];
        // TODO extract this part in something more generic,  we have a quite close case in EE
        foreach ($products as $productIndex => $product) {
            $violations = $this->productValidator->validate($product);
            $productIdentifier = (string) $product->getIdentifier();
            if ($violations->count() !== 0) {
                $this->productDetacher->detach($product);
                unset($validProducts[$productIndex]);
                $productViolations[$productIdentifier] = [];
                foreach ($violations as $violation) {
                    $productViolations[$productIdentifier][] = sprintf(
                        "%s : %s",
                        $violation->getMessage(),
                        $violation->getInvalidValue()
                    );
                }
            }
        }

        return ['products' => $validProducts, 'violations' => $productViolations];
    }
}
