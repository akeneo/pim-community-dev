<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Controller;

use Pim\Bundle\DataGridBundle\Controller\ProductExportController;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use PimEnterprise\Bundle\DataGridBundle\Extension\MassAction\Util\PublishedProductFieldsBuilder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Override ExportController for product exports
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PublishedProductExportController extends ProductExportController
{
    /**
     * @param Request                       $request
     * @param MassActionDispatcher          $massActionDispatcher
     * @param SerializerInterface           $serializer
     * @param ProductRepositoryInterface    $productRepository
     * @param LocaleManager                 $localeManager
     * @param CatalogContext                $catalogContext
     * @param PublishedProductFieldsBuilder $fieldsBuilder
     */
    public function __construct(
        Request $request,
        MassActionDispatcher $massActionDispatcher,
        SerializerInterface $serializer,
        ProductRepositoryInterface $productRepository,
        LocaleManager $localeManager,
        CatalogContext $catalogContext,
        PublishedProductFieldsBuilder $fieldsBuilder
    ) {
        parent::__construct(
            $request,
            $massActionDispatcher,
            $serializer,
            $productRepository,
            $localeManager,
            $catalogContext,
            $fieldsBuilder
        );
    }
}
