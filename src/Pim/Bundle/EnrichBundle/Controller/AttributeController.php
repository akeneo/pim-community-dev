<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\AttributeTypeRegistry;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Factory\AttributeFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Attribute controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeController
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var RouterInterface */
    protected $router;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var HandlerInterface */
    protected $attributeHandler;

    /** @var Form */
    protected $attributeForm;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var VersionManager */
    protected $versionManager;

    /** @var array */
    protected $measuresConfig;

    /** @var array */
    protected $choiceAttributeTypes = [
        AttributeTypes::OPTION_SIMPLE_SELECT,
        AttributeTypes::OPTION_MULTI_SELECT
    ];

    /** @var BulkSaverInterface */
    protected $attributeSaver;

    /** @var RemoverInterface */
    protected $attributeRemover;

    /** @var SaverInterface */
    protected $optionSaver;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var AttributeTypeRegistry */
    protected $registry;

    /** @var AttributeFactory */
    protected $factory;

    /** @var SimpleFactoryInterface */
    protected $optionFactory;

    /** @var SimpleFactoryInterface */
    protected $optionValueFactory;

    /**
     * @param RequestStack                 $requestStack
     * @param RouterInterface              $router
     * @param FormFactoryInterface         $formFactory
     * @param TranslatorInterface          $translator
     * @param HandlerInterface             $attributeHandler
     * @param Form                         $attributeForm
     * @param LocaleRepositoryInterface    $localeRepository
     * @param VersionManager               $versionManager
     * @param BulkSaverInterface           $attributeSaver
     * @param RemoverInterface             $attributeRemover
     * @param SaverInterface               $optionSaver
     * @param AttributeRepositoryInterface $attributeRepository
     * @param GroupRepositoryInterface     $groupRepository
     * @param AttributeTypeRegistry        $registry
     * @param AttributeFactory             $factory
     * @param SimpleFactoryInterface       $optionFactory
     * @param SimpleFactoryInterface       $optionValueFactory
     * @param array                        $measuresConfig
     */
    public function __construct(
        RequestStack $requestStack,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        TranslatorInterface $translator,
        HandlerInterface $attributeHandler,
        Form $attributeForm,
        LocaleRepositoryInterface $localeRepository,
        VersionManager $versionManager,
        BulkSaverInterface $attributeSaver,
        RemoverInterface $attributeRemover,
        SaverInterface $optionSaver,
        AttributeRepositoryInterface $attributeRepository,
        GroupRepositoryInterface $groupRepository,
        AttributeTypeRegistry $registry,
        AttributeFactory $factory,
        SimpleFactoryInterface $optionFactory,
        SimpleFactoryInterface $optionValueFactory,
        $measuresConfig
    ) {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->attributeHandler = $attributeHandler;
        $this->attributeForm = $attributeForm;
        $this->localeRepository = $localeRepository;
        $this->versionManager = $versionManager;
        $this->measuresConfig = $measuresConfig;
        $this->attributeSaver = $attributeSaver;
        $this->attributeRemover = $attributeRemover;
        $this->optionSaver = $optionSaver;
        $this->attributeRepository = $attributeRepository;
        $this->groupRepository = $groupRepository;
        $this->registry = $registry;
        $this->factory = $factory;
        $this->optionFactory = $optionFactory;
        $this->optionValueFactory = $optionValueFactory;
    }

    /**
     * Create attribute
     *
     * @param Request $request
     *
     * @Template("PimEnrichBundle:Attribute:form.html.twig")
     * @AclAncestor("pim_enrich_attribute_create")
     *
     * @return array
     */
    public function createAction(Request $request)
    {
        $attributeType = $request->get('attribute_type');
        $attributeTypes = $this->registry->getAliases();

        if (!$attributeType || !is_string($attributeType) || !in_array($attributeType, $attributeTypes)) {
            return new JsonResponse(['route' => 'pim_enrich_attribute_index']);
        }

        $attribute = $this->factory->createAttribute($attributeType);

        if ($this->attributeHandler->process($attribute)) {
            $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()
                ->add('success', new Message('flash.attribute.created'));

            return new JsonResponse(
                ['route' => 'pim_enrich_attribute_edit', 'params' => ['id' => $attribute->getId()]]
            );
        }

        return [
            'form'            => $this->attributeForm->createView(),
            'locales'         => $this->localeRepository->getActivatedLocaleCodes(),
            'disabledLocales' => $this->localeRepository->findBy(['activated' => false]),
            'measures'        => $this->measuresConfig,
            'type'            => $attributeType
        ];
    }

    /**
     * Edit attribute form
     *
     * @param int $id
     *
     * @Template("PimEnrichBundle:Attribute:form.html.twig")
     * @AclAncestor("pim_enrich_attribute_edit")
     *
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $attribute = $this->findAttributeOr404($id);
        if ($this->attributeHandler->process($attribute)) {
            $request->getSession()
                ->getFlashBag()
                ->add('success', new Message('flash.attribute.updated'));
        }

        return [
            'form'            => $this->attributeForm->createView(),
            'locales'         => $this->localeRepository->getActivatedLocaleCodes(),
            'disabledLocales' => $this->localeRepository->findBy(['activated' => false]),
            'measures'        => $this->measuresConfig,
            'created'         => $this->versionManager->getOldestLogEntry($attribute),
            'updated'         => $this->versionManager->getNewestLogEntry($attribute),
        ];
    }

    /**
     * Create a new option for a simple/multi-select attribute
     *
     * @param Request $request
     * @param int     $id
     * @param string  $dataLocale
     *
     * @Template("PimEnrichBundle:Attribute:form_options.html.twig")
     * @AclAncestor("pim_enrich_attribute_edit")
     *
     * @return Response
     */
    public function createOptionAction(Request $request, $id, $dataLocale)
    {
        $attribute = $this->findAttributeOr404($id);
        if (!in_array($attribute->getType(), $this->choiceAttributeTypes)) {
            return new JsonResponse(
                ['route' => 'pim_enrich_attribute_edit', 'params' => ['id' => $attribute->getId()]]
            );
        }

        $option = $this->optionFactory->create();

        $optionValue = $this->optionValueFactory->create();
        $optionValue->setLocale($dataLocale);
        $optionValue->setValue('');
        $option->addOptionValue($optionValue);

        $attribute->addOption($option);

        $form = $this->formFactory->create('pim_attribute_option_create', $option);

        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->optionSaver->save($option);
                $response = [
                    'status' => 1,
                    'option' => [
                        'id'    => $option->getId(),
                        'label' => $option->setLocale($dataLocale)->__toString()
                    ]
                ];

                return new Response(json_encode($response));
            }
        }

        return [
            'attribute' => $attribute,
            'form'      => $form->createView()
        ];
    }

    /**
     * Remove attribute
     *
     * @param Request $request
     * @param int     $id
     *
     * @AclAncestor("pim_enrich_attribute_remove")
     *
     * @return Response
     */
    public function removeAction(Request $request, $id)
    {
        $attribute = $this->findAttributeOr404($id);
        $this->validateRemoval($attribute);

        $this->attributeRemover->remove($attribute);

        return new Response('', 204);
    }

    /**
     * Find an attribute
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     *
     * @return AttributeInterface
     */
    protected function findAttributeOr404($id)
    {
        $attribute = $this->attributeRepository->find($id);

        if (null === $attribute) {
            throw new NotFoundHttpException(
                sprintf('Attribute %s not found', $id)
            );
        }

        return $attribute;
    }

    /**
     * Check if the attribute is removable, otherwise throw an exception or redirect
     *
     * @param AttributeInterface $attribute
     *
     * @throws DeleteException For ajax requests if the attribute is not removable
     *
     * @return null
     */
    protected function validateRemoval(AttributeInterface $attribute)
    {
        if (AttributeTypes::IDENTIFIER === $attribute->getType()) {
            $errorMessage = 'flash.attribute.identifier not removable';
            $messageParameters = [];
        } else {
            $groupCount = $this->groupRepository->countVariantGroupAxis($attribute);
            if ($groupCount > 0) {
                $errorMessage = 'flash.attribute.used by groups';
                $messageParameters = ['%count%' => $groupCount];
            }
        }

        if (isset($errorMessage)) {
            if ($this->requestStack->getCurrentRequest()->isXmlHttpRequest()) {
                throw new DeleteException($this->translator->trans($errorMessage, $messageParameters));
            } else {
                $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()
                    ->add('error', new Message($errorMessage, $messageParameters));

                return new JsonResponse(['route' => 'pim_enrich_attribute_index']);
            }
        }
    }
}
