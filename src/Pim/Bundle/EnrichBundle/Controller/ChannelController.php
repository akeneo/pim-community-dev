<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Channel controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelController extends AbstractDoctrineController
{
    /** @var Form */
    protected $channelForm;

    /** @var HandlerInterface */
    protected $channelHandler;

    /** @var RemoverInterface */
    protected $channelRemover;

    /** @var BulkSaverInterface */
    protected $localeSaver;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param TokenStorageInterface    $tokenStorage
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param HandlerInterface         $channelHandler
     * @param Form                     $channelForm
     * @param RemoverInterface         $channelRemover
     * @param BulkSaverInterface       $localeSaver
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        HandlerInterface $channelHandler,
        Form $channelForm,
        RemoverInterface $channelRemover,
        BulkSaverInterface $localeSaver
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $tokenStorage,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->channelForm    = $channelForm;
        $this->channelHandler = $channelHandler;
        $this->channelRemover = $channelRemover;
        $this->localeSaver    = $localeSaver;
    }

    /**
     * List channels
     *
     * @Template
     * @AclAncestor("pim_enrich_channel_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Create channel
     *
     * @Template("PimEnrichBundle:Channel:edit.html.twig")
     * @AclAncestor("pim_enrich_channel_create")
     *
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
     * @AclAncestor("pim_enrich_channel_edit")
     *
     * @return array
     */
    public function editAction(Channel $channel)
    {
        if ($this->channelHandler->process($channel)) {
            $this->addFlash('success', 'flash.channel.saved');

            return $this->redirect(
                $this->generateUrl('pim_enrich_channel_edit', ['id' => $channel->getId()])
            );
        }

        return ['form' => $this->channelForm->createView()];
    }

    /**
     * Remove channel
     *
     * @param Request $request
     * @param Channel $channel
     *
     * @AclAncestor("pim_enrich_channel_remove")
     *
     * @return Response
     */
    public function removeAction(Request $request, Channel $channel)
    {
        // @todo This validation should be moved to a validator and that validation trigered by the remover
        $channelCount = $this->getRepository('PimCatalogBundle:Channel')->countAll();
        if ($channelCount <= 1) {
            throw new DeleteException($this->getTranslator()->trans('flash.channel.not removable'));
        }

        $this->channelRemover->remove($channel);

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pim_enrich_channel_index'));
        }
    }
}
