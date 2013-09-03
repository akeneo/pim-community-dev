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
use Pim\Bundle\CatalogBundle\Form\Handler\ChannelHandler;
use Symfony\Component\Form\Form;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Entity\Channel;

/**
 * Channel controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelController extends AbstractDoctrineController
{
    private $datagridWorker;
    private $channelForm;
    private $channelHandler;
    
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        RegistryInterface $doctrine,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        DatagridWorkerInterface $datagridWorker,
        ChannelHandler $channelHandler,
        Form $channelForm
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $doctrine, $formFactory, $validator);
        $this->datagridWorker = $datagridWorker;
        $this->channelForm = $channelForm;
        $this->channelHandler = $channelHandler;
    }
        
    /**
     * List channels
     *
     * @param Request $request
     *
     * @Template
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $queryBuilder = $this->getManager()->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from('PimCatalogBundle:Channel', 'c');

        $datagrid = $this->datagridWorker->getDatagrid('channel', $queryBuilder);

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimCatalogBundle:Channel:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Create channel
     *
     * @Template("PimCatalogBundle:Channel:edit.html.twig")
     * @return array
     */
    public function createAction()
    {
        $channel = new Channel();

        return $this->editAction($channel);
    }

    /**
     * Edit channel
     *
     * @param Channel $channel
     *
     * @Template
     * @return array
     */
    public function editAction(Channel $channel)
    {
        if ($this->channelHandler->process($channel)) {
            $this->addFlash('success', 'Channel successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_catalog_channel_index')
            );
        }

        return array(
            'form' => $this->channelForm->createView()
        );
    }

    /**
     * Remove channel
     *
     * @param Request $request
     * @param Channel $channel
     *
     * @return Response
     */
    public function removeAction(Request $request, Channel $channel)
    {
        $this->getManager()->remove($channel);
        $this->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            $this->addFlash('success', 'Channel successfully removed');

            return $this->redirect($this->generateUrl('pim_catalog_channel_index'));
        }
    }
}
