<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\GridBundle\Helper\DatagridHelperInterface;
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
     * @var DatagridHelperInterface
     */
    private $datagridHelper;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param RegistryInterface        $doctrine
     * @param DatagridHelperInterface  $datagridHelper
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        DatagridHelperInterface $datagridHelper
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->datagridHelper = $datagridHelper;
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

        $datagrid = $this->datagridHelper->getDatagrid('currency', $queryBuilder);

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
