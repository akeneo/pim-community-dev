<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
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

    /**
     * Constructor
     *
     * @param Request              $request
     * @param MassActionDispatcher $massActionDispatcher
     * @param SerializerInterface  $serializer
     * @param ProductManager       $productManager
     * @param LocaleManager        $localeManager
     */
    public function __construct(
        Request $request,
        MassActionDispatcher $massActionDispatcher,
        SerializerInterface $serializer,
        ProductManager $productManager,
        LocaleManager $localeManager
    ) {
        parent::__construct(
            $request,
            $massActionDispatcher,
            $serializer
        );

        $this->productManager = $productManager;
        $this->localeManager  = $localeManager;
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
    protected function quickExportCallback()
    {
        return function () {
            flush();

            $qb = $this->massActionDispatcher->dispatch($this->request);

            $results = $qb->getQuery()->getResults();

            echo $this->serializer->serialize($results, $this->getFormat(), $this->getContext());

            flush();
        };
    }
}
