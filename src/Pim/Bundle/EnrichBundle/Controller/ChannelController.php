<?php

namespace Pim\Bundle\EnrichBundle\Controller;

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

use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Form\Handler\ChannelHandler;

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
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var Form
     */
    protected $channelForm;

    /**
     * @var ChannelHandler
     */
    protected $channelHandler;

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
     * @param LocaleManager            $localeManager
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
        LocaleManager $localeManager,
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

        $this->localeManager  = $localeManager;
        $this->channelForm    = $channelForm;
        $this->channelHandler = $channelHandler;
    }

    /**
     * List channels
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_channel_index")
     * @return Response
     */
    public function indexAction(Request $request)
    {
        return array(
            'localeCode' => $this->localeManager->getUserLocale()->getCode()
        );
    }

    /**
     * Create channel
     *
     * @Template("PimEnrichBundle:Channel:edit.html.twig")
     * @AclAncestor("pim_enrich_channel_create")
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
     * @return array
     */
    public function editAction(Channel $channel)
    {
        if ($this->channelHandler->process($channel)) {
            $this->addFlash('success', 'flash.channel.saved');

            return $this->redirect(
                $this->generateUrl('pim_enrich_channel_edit', array('id' => $channel->getId()))
            );
        }

        return array(
            'form' => $this->channelForm->createView(),
        );
    }

    /**
     * Remove channel
     *
     * @param Request $request
     * @param Channel $channel
     *
     * @AclAncestor("pim_enrich_channel_remove")
     * @return Response
     */
    public function removeAction(Request $request, Channel $channel)
    {
        $channelCount = $this->getRepository('PimCatalogBundle:Channel')->countAll();
        if ($channelCount <= 1) {
            throw new DeleteException($this->getTranslator()->trans('flash.channel.not removable'));
        }

        foreach ($channel->getLocales() as $locale) {
            $channel->removeLocale($locale);
            $this->getManager()->persist($locale);
        }
        $this->getManager()->remove($channel);
        $this->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pim_enrich_channel_index'));
        }
    }
}
