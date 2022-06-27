<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi\ProductPdfController as BaseController;
use Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\RendererRegistry;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Permission\Bundle\User\UserContext;
use Akeneo\Pim\Permission\Component\Attributes;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Product Controller
 *
 * @author Charles Pourcel <charles.pourcel@akeneo.com>
 */
class ProductController extends BaseController
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var RendererRegistry */
    protected $rendererRegistry;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var UserContext */
    protected $userContext;

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface    $productRepository
     * @param RendererRegistry              $rendererRegistry
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param UserContext                   $userContext
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        RendererRegistry $rendererRegistry,
        AuthorizationCheckerInterface $authorizationChecker,
        UserContext $userContext
    ) {
        parent::__construct($productRepository, $rendererRegistry);

        $this->authorizationChecker = $authorizationChecker;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     *
     * @AclAncestor("pim_pdf_generator_product_download")
     */
    public function downloadPdfAction(Request $request, string $uuid)
    {
        $locale = $this->userContext->getCurrentLocale();
        $viewLocaleGranted = $this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale);
        if (!$viewLocaleGranted) {
            throw new AccessDeniedException();
        }

        return parent::downloadPdfAction($request, $uuid);
    }
}
