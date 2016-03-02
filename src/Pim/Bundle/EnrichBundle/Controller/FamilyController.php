<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Factory\FamilyFactory;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Enrich\Model\AvailableAttributes;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Family controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyController
{
    /** @var Request */
    protected $request;

    /** @var RouterInterface */
    protected $router;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var EngineInterface */
    protected $templating;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var ChannelManager */
    protected $channelManager;

    /** @var FamilyFactory */
    protected $familyFactory;

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

    /** @var string */
    protected $familyClass;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepo;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /**
     * @param Request                      $request
     * @param EngineInterface              $templating
     * @param RouterInterface              $router
     * @param FormFactoryInterface         $formFactory
     * @param TranslatorInterface          $translator
     * @param ManagerRegistry              $doctrine
     * @param ChannelManager               $channelManager
     * @param FamilyFactory                $familyFactory
     * @param HandlerInterface             $familyHandler
     * @param Form                         $familyForm
     * @param SaverInterface               $familySaver
     * @param RemoverInterface             $familyRemover
     * @param AttributeRepositoryInterface $attributeRepo
     * @param FamilyRepositoryInterface    $familyRepository
     * @param string                       $attributeClass
     * @param string                       $familyClass
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        ManagerRegistry $doctrine,
        ChannelManager $channelManager,
        FamilyFactory $familyFactory,
        HandlerInterface $familyHandler,
        Form $familyForm,
        SaverInterface $familySaver,
        RemoverInterface $familyRemover,
        AttributeRepositoryInterface $attributeRepo,
        FamilyRepositoryInterface $familyRepository,
        $attributeClass,
        $familyClass
    ) {
        $this->request          = $request;
        $this->templating       = $templating;
        $this->router           = $router;
        $this->formFactory      = $formFactory;
        $this->translator       = $translator;
        $this->doctrine         = $doctrine;
        $this->channelManager   = $channelManager;
        $this->familyFactory    = $familyFactory;
        $this->familyHandler    = $familyHandler;
        $this->familyForm       = $familyForm;
        $this->attributeClass   = $attributeClass;
        $this->familySaver      = $familySaver;
        $this->familyRemover    = $familyRemover;
        $this->familyClass      = $familyClass;
        $this->attributeRepo    = $attributeRepo;
        $this->familyRepository = $familyRepository;
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
        if (!$this->request->isXmlHttpRequest()) {
            return new RedirectResponse($this->router->generate('pim_enrich_family_index'));
        }

        $family = $this->familyFactory->createFamily();

        if ($this->familyHandler->process($family)) {
            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.family.created'));

            $response = [
                'status' => 1,
                'url'    => $this->router->generate('pim_enrich_family_edit', ['id' => $family->getId()])
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
     * @param int $id
     *
     * @Template
     * @AclAncestor("pim_enrich_family_index")
     *
     * @return array
     */
    public function editAction($id)
    {
        $family = $this->familyRepository->find($id);

        if (null === $family) {
            throw new NotFoundHttpException(sprintf('%s entity not found', $this->familyClass));
        }

        if ($this->familyHandler->process($family)) {
            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.family.updated'));
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
     * @param int $id
     *
     * @AclAncestor("pim_enrich_family_history")
     *
     * @return Response
     */
    public function historyAction($id)
    {
        $family = $this->familyRepository->find($id);

        if (null === $family) {
            throw new NotFoundHttpException(sprintf('%s entity not found', $this->familyClass));
        }

        return $this->templating->renderResponse(
            'PimEnrichBundle:Family:_history.html.twig',
            [
                'family' => $family
            ]
        );
    }

    /**
     * Remove a family
     *
     * @param int $id
     *
     * @AclAncestor("pim_enrich_family_remove")
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction($id)
    {
        $family = $this->familyRepository->find($id);

        if (null === $family) {
            throw new NotFoundHttpException(sprintf('%s entity not found', $this->familyClass));
        }

        $this->familyRemover->remove($family);

        if ($this->request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return new RedirectResponse($this->router->generate('pim_enrich_family_index'));
        }
    }

    /**
     * Add attributes to a family
     *
     * @param int $id
     *
     * @AclAncestor("pim_enrich_family_edit_attributes")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAttributesAction($id)
    {
        $family = $this->familyRepository->find($id);

        if (null === $family) {
            throw new NotFoundHttpException(sprintf('%s entity not found', $this->familyClass));
        }

        $availableAttributes = new AvailableAttributes();
        $attributesForm      = $this->getAvailableAttributesForm(
            $family->getAttributes()->toArray(),
            $availableAttributes
        );

        $attributesForm->submit($this->request);
        foreach ($availableAttributes->getAttributes() as $attribute) {
            $family->addAttribute($attribute);
        }

        $this->familySaver->save($family);

        $this->request->getSession()->getFlashBag()->add('success', new Message('flash.family.attributes added'));

        return new RedirectResponse($this->router->generate('pim_enrich_family_edit', ['id' => $family->getId()]));
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
        $family = $this->familyRepository->find($familyId);

        if (null === $family) {
            throw new NotFoundHttpException(sprintf('%s entity not found', $this->familyClass));
        }

        $attribute = $this->attributeRepo->find($attributeId);

        if (null === $attribute) {
            throw new NotFoundHttpException(sprintf('%s entity not found', $this->attributeClass));
        }

        if (false === $family->hasAttribute($attribute)) {
            throw new DeleteException($this->translator->trans('flash.family.attribute not found'));
        } elseif (AttributeTypes::IDENTIFIER === $attribute->getAttributeType()) {
            throw new DeleteException($this->translator->trans('flash.family.identifier not removable'));
        } elseif ($attribute === $family->getAttributeAsLabel()) {
            throw new DeleteException($this->translator->trans('flash.family.label attribute not removable'));
        } else {
            $family->removeAttribute($attribute);
            foreach ($family->getAttributeRequirements() as $requirement) {
                if ($requirement->getAttribute() === $attribute) {
                    $family->removeAttributeRequirement($requirement);
                    $this->doctrine->getManagerForClass(ClassUtils::getClass($requirement))->remove($requirement);
                }
            }

            $this->familySaver->save($family);
        }
        if ($this->request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return new RedirectResponse($this->router->generate('pim_enrich_family_edit', ['id' => $family->getId()]));
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
        return $this->formFactory->create(
            'pim_available_attributes',
            $availableAttributes ?: new AvailableAttributes(),
            ['excluded_attributes' => $attributes]
        );
    }
}
