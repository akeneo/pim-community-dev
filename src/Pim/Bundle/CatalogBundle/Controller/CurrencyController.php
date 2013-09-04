<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Entity\Currency;

/**
 * Currency controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyController extends AbstractDoctrineController
{
    /**
     * @var DatagridWorkerInterface
     */
    private $datagridWorker;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param RegistryInterface        $doctrine
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param DatagridWorkerInterface  $datagridWorker
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        RegistryInterface $doctrine,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        DatagridWorkerInterface $datagridWorker
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $doctrine, $formFactory, $validator);
        $this->datagridWorker = $datagridWorker;
    }
    /**
     * List currencies
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $queryBuilder = $this->getManager()->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from('PimCatalogBundle:Currency', 'c');

        $datagrid = $this->datagridWorker->getDatagrid('currency', $queryBuilder);

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimCatalogBundle:Currency:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Activate/Desactivate a currency
     *
     * @param Currency $currency
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleAction(Currency $currency)
    {
        try {
            $currency->toggleActivation();
            $this->getManager()->flush();

            $this->addFlash('success', 'Currency is successfully updated.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Action failed. Please retry.');
        }

        return $this->redirect($this->generateUrl('pim_catalog_currency_index'));
    }
}
