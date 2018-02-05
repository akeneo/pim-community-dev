<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Channel controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChannelController
{
    /** @var Request */
    protected $request;

    /** @var RouterInterface */
    protected $router;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var Form */
    protected $channelForm;

    /** @var HandlerInterface */
    protected $channelHandler;

    /** @var RemoverInterface */
    protected $channelRemover;

    /** @var BulkSaverInterface */
    protected $localeSaver;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /**
     * @param Request                    $request
     * @param RouterInterface            $router
     * @param TranslatorInterface        $translator
     * @param HandlerInterface           $channelHandler
     * @param Form                       $channelForm
     * @param RemoverInterface           $channelRemover
     * @param BulkSaverInterface         $localeSaver
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(
        Request $request,
        RouterInterface $router,
        TranslatorInterface $translator,
        HandlerInterface $channelHandler,
        Form $channelForm,
        RemoverInterface $channelRemover,
        BulkSaverInterface $localeSaver,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->request = $request;
        $this->router = $router;
        $this->translator = $translator;
        $this->channelForm = $channelForm;
        $this->channelHandler = $channelHandler;
        $this->channelRemover = $channelRemover;
        $this->localeSaver = $localeSaver;
        $this->channelRepository = $channelRepository;
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
            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.channel.saved'));

            return new RedirectResponse(
                $this->router->generate('pim_enrich_channel_edit', ['id' => $channel->getId()])
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
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        // TODO This validation should be moved to a validator and that validation triggered by the remover
        $channelCount = $this->channelRepository->countAll();
        if ($channelCount <= 1) {
            throw new DeleteException($this->translator->trans('flash.channel.not removable'));
        }

        $this->channelRemover->remove($channel);

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return new RedirectResponse($this->router->generate('pim_enrich_channel_index'));
        }
    }
}
