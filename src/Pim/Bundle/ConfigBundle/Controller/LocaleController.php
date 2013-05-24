<?php
namespace Pim\Bundle\ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

use Pim\Bundle\ConfigBundle\Entity\Locale;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Locale controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/locale")
 */
class LocaleController extends Controller
{

    /**
     * List locales
     *
     * @param Request $request
     *
     * @Route(
     *     "/index.{_format}",
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
            ->select('l')
            ->from('PimConfigBundle:Locale', 'l');

        /** @var $queryFactory QueryFactory */
        $queryFactory = $this->get('pim_config.datagrid.manager.locale.default_query_factory');
        $queryFactory->setQueryBuilder($queryBuilder);

        /** @var $datagridManager LocaleDatagridManager */
        $datagridManager = $this->get('pim_config.datagrid.manager.locale');
        $datagrid = $datagridManager->getDatagrid();

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimConfigBundle:Locale:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
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

    /**
     * Get locale repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getLocaleRepository()
    {
        return $this->getEntityManager()->getRepository('PimConfigBundle:Locale');
    }

    /**
     * Create locale
     *
     * @Route("/create")
     * @Template("PimConfigBundle:Locale:edit.html.twig")
     *
     * @return array
     */
    public function createAction()
    {
        $locale = new Locale();

        return $this->editAction($locale);
    }

    /**
     * Edit locale
     *
     * @param Locale $locale
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     *
     * @return array
     */
    public function editAction(Locale $locale)
    {
        if ($this->get('pim_config.form.handler.locale')->process($locale)) {
            $this->get('session')->getFlashBag()->add('success', 'Locale successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_config_locale_index')
            );
        }

        return array(
            'form' => $this->get('pim_config.form.locale')->createView()
        );
    }

    /**
     * Disable locale
     *
     * @param Locale $locale
     *
     * @Route("/disable/{id}", requirements={"id"="\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function disableAction(Locale $locale)
    {
        // Disable activated property
        $locale->setActivated(false);
        $this->getEntityManager()->persist($locale);
        $this->getEntityManager()->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pim_config_locale_index'));
        }
    }
}
