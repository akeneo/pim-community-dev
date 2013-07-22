<?php

namespace Pim\Bundle\ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\ConfigBundle\Entity\Currency;

/**
 * Currency controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/currency")
 */
class CurrencyController extends Controller
{
    /**
     * List currencies
     *
     * @param Request $request
     *
     * @Route(
     *     "/.{_format}",
     *     requirements={"_format"="html|json"},
     *     defaults={"_format" = "html"}
     * )
     * @Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var $queryBuilder QueryBuilder */
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from('PimConfigBundle:Currency', 'c');

        /** @var $queryFactory QueryFactory */
        $queryFactory = $this->get('pim_config.datagrid.manager.currency.default_query_factory');
        $queryFactory->setQueryBuilder($queryBuilder);

        /** @var $datagridManager LocaleDatagridManager */
        $datagridManager = $this->get('pim_config.datagrid.manager.currency');
        $datagrid = $datagridManager->getDatagrid();

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimConfigBundle:Currency:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Activate/Desactivate a currency
     *
     * @param Currency $currency
     *
     * @Route("/{id}/toggle", requirements={"id"="\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleAction(Currency $currency)
    {
        try {
            $currency->toggleActivation();
            $this->getEntityManager()->flush();

            $this->get('session')->getFlashBag()->add('success', 'Currency is successfully updated.');
        } catch (\Exception $e) {
            $this->get('session')->getFlashbag()->add('error', 'Action failed. Please retry.');
        }

        return $this->redirect($this->generateUrl('pim_config_currency_index'));
    }

    /**
     * Get entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }
}
