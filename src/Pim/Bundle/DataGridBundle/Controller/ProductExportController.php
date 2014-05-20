<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Pim\Bundle\DataGridBundle\Extension\MassAction\Util\ProductFieldsBuilder;
use Pim\Bundle\TransformBundle\Normalizer\FlatProductNormalizer;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Override ExportController for product exports
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductExportController extends ExportController
{
    /** @var ProductManager $productManager */
    protected $productManager;

    /** @var LocaleManager $localeManager */
    protected $localeManager;

    /** @var CatalogContext $catalogContext */
    protected $catalogContext;

    /** @var ProductFieldsBuilder $fieldsBuilder */
    protected $fieldsBuilder;

    /**
     * Constructor
     *
     * @param Request                $request
     * @param MassActionDispatcher   $massActionDispatcher
     * @param SerializerInterface    $serializer
     * @param ProductManager         $productManager
     * @param LocaleManager          $localeManager
     * @param CatalogContext         $catalogContext
     */
    public function __construct(
        Request $request,
        MassActionDispatcher $massActionDispatcher,
        SerializerInterface $serializer,
        ProductManager $productManager,
        LocaleManager $localeManager,
        CatalogContext $catalogContext,
        ProductFieldsBuilder $fieldsBuilder
    ) {
        parent::__construct(
            $request,
            $massActionDispatcher,
            $serializer
        );

        $this->productManager   = $productManager;
        $this->localeManager    = $localeManager;
        $this->catalogContext   = $catalogContext;
        $this->fieldsBuilder    = $fieldsBuilder;
    }

    /**
     * {@inheritdoc}
     */
    protected function createFilename()
    {
        $dateTime = new \DateTime();

        return sprintf(
            'products_export_%s_%s_%s.%s',
            $this->catalogContext->getLocaleCode(),
            $this->catalogContext->getScopeCode(),
            $dateTime->format('Y-m-d_H-i-s'),
            $this->getFormat()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function quickExport()
    {
        $productIds   = $this->massActionDispatcher->dispatch($this->request);
        $fieldsList   = $this->fieldsBuilder->getFieldsList($productIds);
        $attributeIds = $this->fieldsBuilder->getAttributeIds();
        $context      = $this->getContext() + ['fields' => $fieldsList];
        $productRepo  = $this->productManager->getProductRepository();

        // batch output to avoid memory leak
        $offset = 0;
        $batchSize = 100;
        while ($productsList = array_slice($productIds, $offset, $batchSize)) {
            $results = $productRepo->getFullProducts($productsList, $attributeIds);
            echo $this->serializer->serialize($results, $this->getFormat(), $context);
            $offset += $batchSize;
            flush();
        }
    }
}
