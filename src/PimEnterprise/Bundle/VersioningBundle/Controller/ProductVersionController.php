<?php

namespace PimEnterprise\Bundle\VersioningBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\VersioningBundle\Model\Version;
use PimEnterprise\Bundle\VersioningBundle\Reverter\ProductReverter;

/**
 * Product version controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
     * @param Version $version
     *
     * @return RedirectResponse|JsonResponse
     *
     * @AclAncestor("pimee_versioning_product_version_revert")
     */
    public function revertAction($id)
    {
        $version = $this->findOr404($this->versionClass, $id);
        $this->reverter->revert($version);

        $this->addFlash('success', 'flash.version.revert.product');

        return $this->redirectToRoute('pim_enrich_product_edit', ['id' => $version->getResourceId()]);
    }
}
