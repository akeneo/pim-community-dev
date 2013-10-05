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
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Form\Handler\ChannelHandler;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Exception\DeleteException;
use Pim\Bundle\CatalogBundle\Exception\LastAttributeOptionDeletedException;

/**
 * Channel controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelController extends AbstractDoctrineController
{
    /**
     * @var DatagridWorkerInterface
     */
    private $datagridWorker;

    /**
     * @var Form
     */
    private $channelForm;

    /**
     * @var ChannelHandler
     */
    private $channelHandler;

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
     * @param DatagridWorkerInterface  $datagridWorker
     * @param ChannelHandler           $channelHandler
     * @param Form                     $channelForm
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
        DatagridWorkerInterface $datagridWorker,
        ChannelHandler $channelHandler,
        Form $channelForm
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

        $this->datagridWorker = $datagridWorker;
        $this->channelForm    = $channelForm;
        $this->channelHandler = $channelHandler;
    }

    /**
     * List channels
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_channel_index")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var $queryBuilder QueryBuilder */
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
     * @AclAncestor("pim_catalog_channel_create")
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
     * @AclAncestor("pim_catalog_channel_edit")
     * @return array
     */
    public function editAction(Channel $channel)
    {
        if ($this->channelHandler->process($channel)) {
            $this->addFlash('success', 'flash.channel.saved');

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
     * @AclAncestor("pim_catalog_channel_remove")
     * @return Response
     */
    public function removeAction(Request $request, Channel $channel)
    {
        try {
            $this->getManager()->remove($channel);
            $this->getManager()->flush();
        } catch (LastAttributeOptionDeletedException $ex) {
            throw new DeleteException($this->getTranslator()->trans('flash.channel.not removable'));
        }

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pim_catalog_channel_index'));
        }
    }
}
