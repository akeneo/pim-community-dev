<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Form\Handler\LocaleHandler;
use Symfony\Component\Form\Form;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Entity\Locale;

/**
 * Locale controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleController extends AbstractDoctrineController
{
    private $datagridWorker;
    private $localeForm;
    private $localeHandler;
    
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        RegistryInterface $doctrine,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        DatagridWorkerInterface $datagridWorker,
        LocaleHandler $localeHandler,
        Form $localeForm
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $doctrine, $formFactory, $validator);
        $this->datagridWorker = $datagridWorker;
        $this->localeForm = $localeForm;
        $this->localeHandler = $localeHandler;
    }
    
    /**
     * List locales
     *
     * @param Request $request
     *
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
     *
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
     *
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
