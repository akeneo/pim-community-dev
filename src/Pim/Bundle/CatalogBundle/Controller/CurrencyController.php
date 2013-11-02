<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

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
     * @param DatagridWorkerInterface $datagridWorker
     */
    public function __construct(DatagridWorkerInterface $datagridWorker)
    {
        $this->datagridWorker = $datagridWorker;
    }
    /**
     * List currencies
     *
     * @param Request $request
     *
     * @AclAncestor("pim_catalog_currency_index")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var $queryBuilder QueryBuilder */
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
     * @AclAncestor("pim_catalog_currency_toggle")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleAction(Currency $currency)
    {
        try {
            $currency->toggleActivation();
            $this->getManager()->flush();

            $this->addFlash('success', 'flash.currency.updated');
        } catch (\Exception $e) {
            $this->addFlash('error', 'flash.error ocurred');
        }

        return $this->redirect($this->generateUrl('pim_catalog_currency_index'));
    }
}
