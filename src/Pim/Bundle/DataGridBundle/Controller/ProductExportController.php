<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\AssociationTypeManager;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Pim\Bundle\TransformBundle\Normalizer\FlatProductNormalizer;

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

    /** @var CurrencyManager $currencyManager */
    protected $currencyManager;

    /** @var AssociationTypeManager $assocTypeManager */
    protected $assocTypeManager;

    /**
     * Constructor
     *
     * @param Request                $request
     * @param MassActionDispatcher   $massActionDispatcher
     * @param SerializerInterface    $serializer
     * @param ProductManager         $productManager
     * @param LocaleManager          $localeManager
     * @param CurrencyManager        $currencyManager
     * @param AssociationTypeManager $assocTypeManager
     */
    public function __construct(
        Request $request,
        MassActionDispatcher $massActionDispatcher,
        SerializerInterface $serializer,
        ProductManager $productManager,
        LocaleManager $localeManager,
        CurrencyManager $currencyManager,
        AssociationTypeManager $assocTypeManager
    ) {
        parent::__construct(
            $request,
            $massActionDispatcher,
            $serializer
        );

        $this->productManager   = $productManager;
        $this->localeManager    = $localeManager;
        $this->currencyManager  = $currencyManager;
        $this->assocTypeManager = $assocTypeManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function createFilename()
    {
        $dateTime = new \DateTime();

        return sprintf(
            'products_export_%s_%s_%s.%s',
            $this->productManager->getLocale(),
            $this->productManager->getScope(),
            $dateTime->format('Y-m-d_H-i-s'),
            $this->getFormat()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function quickExport()
    {
        $productIds = $this->massActionDispatcher->dispatch($this->request);

        // get attributes
        $productRepo    = $this->productManager->getProductRepository();
        $attributeRepo  = $this->productManager->getAttributeRepository();
        $attributeIds   = $productRepo->getAvailableAttributeIdsToExport($productIds);
        $attributesList = $attributeRepo->findBy(array('id' => $attributeIds));

        // prepare context from attributes list
        $fieldsList = $this->prepareFieldsList($attributesList);
        $context    = $this->getContext() + ['fields' => $fieldsList];

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

    /**
     * Prepare fields list for CSV headers
     *
     * @param array $attributesList
     *
     * @return array
     */
    protected function prepareFieldsList(array $attributesList = array())
    {
        $fieldsList = $this->prepareAttributesList($attributesList);
        $fieldsList[] = FlatProductNormalizer::FIELD_FAMILY;
        $fieldsList[] = FlatProductNormalizer::FIELD_CATEGORY;
        $fieldsList[] = FlatProductNormalizer::FIELD_GROUPS;

        $associationTypes = $this->assocTypeManager->getAssociationTypes();
        foreach ($associationTypes as $associationType) {
            $fieldsList[] = sprintf('%s-groups', $associationType->getCode());
            $fieldsList[] = sprintf('%s-products', $associationType->getCode());
        }

        return $fieldsList;
    }

    /**
     * Prepare attributes list for CSV headers
     *
     * @param array $attributesList
     *
     * @return array
     */
    protected function prepareAttributesList(array $attributesList)
    {
        $scopeCode   = $this->productManager->getScope();
        $localeCodes = $this->localeManager->getActiveCodes();
        $fieldsList  = array();

        foreach ($attributesList as $attribute) {
            $attCode = $attribute->getCode();
            if ($attribute->isLocalizable() && $attribute->isScopable()) {
                foreach ($localeCodes as $localeCode) {
                    $fieldsList[] = sprintf('%s-%s-%s', $attCode, $localeCode, $scopeCode);
                }
            } elseif ($attribute->isLocalizable()) {
                foreach ($localeCodes as $localeCode) {
                    $fieldsList[] = sprintf('%s-%s', $attCode, $localeCode);
                }
            } elseif ($attribute->isScopable()) {
                $fieldsList[] = sprintf('%s-%s', $attCode, $scopeCode);
            } elseif ($attribute->getAttributeType() === 'pim_catalog_identifier') {
                array_unshift($fieldsList, $attCode);
            } elseif ($attribute->getAttributeType() === 'pim_catalog_price_collection') {
                foreach ($this->currencyManager->getActiveCodeChoices() as $currencyCode) {
                    $fieldsList[] = sprintf('%s-%s', $attCode, $currencyCode);
                }
            } else {
                $fieldsList[] = $attCode;
            }
        }

        return $fieldsList;
    }
}
