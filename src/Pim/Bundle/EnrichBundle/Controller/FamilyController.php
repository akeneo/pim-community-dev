<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\FamilyManager;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
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
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Family controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyController extends AbstractDoctrineController
{
    /** @var FamilyManager */
    protected $familyManager;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var FamilyFactory */
    protected $factory;

    /** @var HandlerInterface */
    protected $familyHandler;

    /** @var Form */
    protected $familyForm;

    /** @var string */
    protected $attributeClass;

    /** @var SaverInterface */
    protected $familySaver;

    /** @var RemoverInterface */
    protected $familyRemover;

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
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param FamilyManager            $familyManager
     * @param ChannelManager           $channelManager
     * @param FamilyFactory            $factory
     * @param HandlerInterface         $familyHandler
     * @param Form                     $familyForm
     * @param SaverInterface           $familySaver
     * @param RemoverInterface         $familyRemover
     * @param string                   $attributeClass
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        FamilyManager $familyManager,
        ChannelManager $channelManager,
        FamilyFactory $factory,
        HandlerInterface $familyHandler,
        Form $familyForm,
        SaverInterface $familySaver,
        RemoverInterface $familyRemover,
        $attributeClass
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->familyManager  = $familyManager;
        $this->channelManager = $channelManager;
        $this->factory        = $factory;
        $this->familyHandler  = $familyHandler;
        $this->familyForm     = $familyForm;
        $this->attributeClass = $attributeClass;
        $this->familySaver    = $familySaver;
        $this->familyRemover  = $familyRemover;
    }

    /**
     * List families
     *
     * @Template
     * @AclAncestor("pim_enrich_family_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * Create a family
     *
     * @Template
     * @AclAncestor("pim_enrich_family_create")
     *
     * @return array
     */
    public function createAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_enrich_family_index');
        }

        $family = $this->factory->createFamily();

        if ($this->familyHandler->process($family)) {
            $this->addFlash('success', 'flash.family.created');

            $response = [
                'status' => 1,
                'url'    => $this->generateUrl('pim_enrich_family_edit', ['id' => $family->getId()])
            ];

            return new Response(json_encode($response));
        }

        return [
            'form' => $this->familyForm->createView()
        ];
    }

    /**
     * Edit a family
     *
     * TODO : find a way to use param converter with interfaces
     *
     * @param Family $family
     *
     * @Template
     * @AclAncestor("pim_enrich_family_index")
     *
     * @return array
     */
    public function editAction(Family $family)
    {
        if ($this->familyHandler->process($family)) {
            $this->addFlash('success', 'flash.family.updated');
        }

        return [
            'form'            => $this->familyForm->createView(),
            'attributesForm'  => $this->getAvailableAttributesForm(
                $family->getAttributes()->toArray()
            )->createView(),
            'channels' => $this->channelManager->getChannels()
        ];
    }

    /**
     * History of a family
     *
     * TODO : find a way to use param converter with interfaces
     *
     * @param Family $family
     *
     * @AclAncestor("pim_enrich_family_history")
     *
     * @return Response
     */
    public function historyAction(Family $family)
    {
        return $this->render(
            'PimEnrichBundle:Family:_history.html.twig',
            [
                'family' => $family
            ]
        );
    }

    /**
     * Remove a family
     *
     * @param Family $family
     *
     * @AclAncestor("pim_enrich_family_remove")
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Family $family)
    {
        $this->familyRemover->remove($family);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_family_index');
        }
    }

    /**
     * Add attributes to a family
     *
     * @param Family $family
     *
     * @AclAncestor("pim_enrich_family_edit_attributes")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAttributesAction(Family $family)
    {
        $availableAttributes = new AvailableAttributes();
        $attributesForm      = $this->getAvailableAttributesForm(
            $family->getAttributes()->toArray(),
            $availableAttributes
        );

        $attributesForm->submit($this->getRequest());
        foreach ($availableAttributes->getAttributes() as $attribute) {
            $family->addAttribute($attribute);
        }

        $this->familySaver->save($family);

        $this->addFlash('success', 'flash.family.attributes added');

        return $this->redirectToRoute('pim_enrich_family_edit', ['id' => $family->getId()]);
    }

    /**
     * Remove an attribute
     *
     * @param int $familyId
     * @param int $attributeId
     *
     * @AclAncestor("pim_enrich_family_edit_attributes")
     *
     * @throws DeleteException
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAttributeAction($familyId, $attributeId)
    {
        $family    = $this->findOr404('PimCatalogBundle:Family', $familyId);
        $attribute = $this->findOr404($this->attributeClass, $attributeId);

        if (false === $family->hasAttribute($attribute)) {
            throw new DeleteException($this->getTranslator()->trans('flash.family.attribute not found'));
        } elseif ($attribute->getAttributeType() === 'pim_catalog_identifier') {
            throw new DeleteException($this->getTranslator()->trans('flash.family.identifier not removable'));
        } elseif ($attribute === $family->getAttributeAsLabel()) {
            throw new DeleteException($this->getTranslator()->trans('flash.family.label attribute not removable'));
        } else {
            $family->removeAttribute($attribute);
            foreach ($family->getAttributeRequirements() as $requirement) {
                if ($requirement->getAttribute() === $attribute) {
                    $family->removeAttributeRequirement($requirement);
                }
            }

            $this->familySaver->save($family);
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_family_edit', ['id' => $family->getId()]);
        }
    }

    /**
     * Get the AvailableAttributes form
     *
     * @param array               $attributes          The attributes
     * @param AvailableAttributes $availableAttributes The available attributes container
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getAvailableAttributesForm(
        array $attributes = [],
        AvailableAttributes $availableAttributes = null
    ) {
        return $this->createForm(
            'pim_available_attributes',
            $availableAttributes ?: new AvailableAttributes(),
            ['excluded_attributes' => $attributes]
        );
    }
}
