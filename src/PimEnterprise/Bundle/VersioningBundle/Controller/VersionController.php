<?php

namespace PimEnterprise\Bundle\VersioningBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\VersioningBundle\Model\Version;
use PimEnterprise\Bundle\VersioningBundle\Reverter\VersionReverter;

/**
 * Version controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class VersionController extends AbstractDoctrineController
{
    /** @var VersionReverter */
    protected $reverter;

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
     * @param VersionReverter          $reverter
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
        VersionReverter $reverter
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

        $this->reverter = $reverter;
    }

    /**
     * Revert the entity to the current version
     *
     * @param Version $version
     */
    public function revertAction(Version $version)
    {
        $this->reverter->revert($version);

        return $this->redirectToRoute('pim_enrich_product_edit', ['id' => $version->getResourceId()]);
    }
}
