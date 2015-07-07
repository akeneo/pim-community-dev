<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\VersioningBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use PimEnterprise\Bundle\VersioningBundle\Reverter\ProductReverter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Product version controller
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductVersionController extends AbstractDoctrineController
{
    /** @var ProductReverter */
    protected $reverter;

    /** @var string */
    protected $versionClass;

    /**
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param string                   $versionClass
     * @param ProductReverter          $reverter
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        $versionClass,
        ProductReverter $reverter
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->versionClass = $versionClass;
        $this->reverter = $reverter;
    }

    /**
     * Revert the entity to the current version
     *
     * @param string|integer $id
     *
     * @return RedirectResponse
     *
     * @AclAncestor("pimee_versioning_product_version_revert")
     */
    public function revertAction($id)
    {
        try {
            $version = $this->findOr404($this->versionClass, $id);
            $this->reverter->revert($version);

            $this->addFlash('success', 'flash.version.revert.product');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('pim_enrich_product_edit', ['id' => $version->getResourceId()]);
    }
}
