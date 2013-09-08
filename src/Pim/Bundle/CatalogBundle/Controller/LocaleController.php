<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Form\Handler\LocaleHandler;

/**
 * Locale controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_catalog_locale",
 *      name="Locale manipulation",
 *      description="Locale manipulation",
 *      parent="pim_catalog"
 * )
 */
class LocaleController extends AbstractDoctrineController
{
    /**
     * @var DatagridWorkerInterface
     */
    private $datagridWorker;

    /**
     * @var Form
     */
    private $localeForm;

    /**
     * @var LocaleHandler
     */
    private $localeHandler;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param RegistryInterface        $doctrine
     * @param DatagridWorkerInterface  $datagridWorker
     * @param LocaleHandler            $localeHandler
     * @param Form                     $localeForm
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        RegistryInterface $doctrine,
        DatagridWorkerInterface $datagridWorker,
        LocaleHandler $localeHandler,
        Form $localeForm
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $formFactory, $validator, $doctrine);

        $this->datagridWorker = $datagridWorker;
        $this->localeForm     = $localeForm;
        $this->localeHandler  = $localeHandler;
    }

    /**
     * List locales
     *
     * @param Request $request
     * @Acl(
     *      id="pim_catalog_locale_index",
     *      name="View locale list",
     *      description="View locale list",
     *      parent="pim_catalog_locale"
     * )
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var $queryBuilder QueryBuilder */
        $queryBuilder = $this->getManager()->createQueryBuilder();
        $queryBuilder
            ->select('l')
            ->from('PimCatalogBundle:Locale', 'l');

        $datagrid = $this->datagridWorker->getDatagrid('locale', $queryBuilder);

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimCatalogBundle:Locale:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Edit locale
     *
     * @param Locale $locale
     *
     * @Template
     * @Acl(
     *      id="pim_catalog_locale_edit",
     *      name="Edit a locale",
     *      description="Edit a locale",
     *      parent="pim_catalog_locale"
     * )
     * @return array
     */
    public function editAction(Locale $locale)
    {
        if ($this->localeHandler->process($locale)) {
            $this->addFlash('success', 'Locale successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_catalog_locale_index')
            );
        }

        return array(
            'form' => $this->localeForm->createView()
        );
    }

    /**
     * Disable locale
     *
     * @param Request $request
     * @param Locale  $locale
     * @Acl(
     *      id="pim_catalog_locale_disable",
     *      name="Disable a locale",
     *      description="Disable a locale",
     *      parent="pim_catalog_locale"
     * )
     * @return Response
     */
    public function disableAction(Request $request, Locale $locale)
    {
        $locale->setActivated(false);
        $this->getManager()->persist($locale);
        $this->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pim_catalog_locale_index'));
        }
    }

    /**
     * Enable locale
     *
     * @param Request $request
     * @param Locale  $locale
     * @Acl(
     *      id="pim_catalog_locale_enable",
     *      name="Enable a locale",
     *      description="Enable a locale",
     *      parent="pim_catalog_locale"
     * )
     * @return Response
     */
    public function enableAction(Request $request, Locale $locale)
    {
        $locale->setActivated(true);
        $this->getManager()->persist($locale);
        $this->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pim_catalog_locale_index'));
        }
    }
}
