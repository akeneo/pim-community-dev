<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Form\Handler\HandlerInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Attribute controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeController extends AbstractDoctrineController
{
    /** @var HandlerInterface */
    protected $attributeHandler;

    /** @var Form */
    protected $attributeForm;

    /** @var AttributeManager */
    protected $attributeManager;

    /** @var AttributeOptionManager */
    protected $optionManager;

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

    /**
     * Constructor
     *
     * @param Request                      $request
     * @param EngineInterface              $templating
     * @param RouterInterface              $router
     * @param TokenStorageInterface        $tokenStorage
     * @param FormFactoryInterface         $formFactory
     * @param ValidatorInterface           $validator
     * @param TranslatorInterface          $translator
     * @param EventDispatcherInterface     $eventDispatcher
     * @param ManagerRegistry              $doctrine
     * @param HandlerInterface             $attributeHandler
     * @param Form                         $attributeForm
     * @param AttributeManager             $attributeManager
     * @param AttributeOptionManager       $optionManager
     * @param LocaleRepositoryInterface    $localeRepository
     * @param VersionManager               $versionManager
     * @param BulkSaverInterface           $attributeSaver
     * @param RemoverInterface             $attributeRemover
     * @param SaverInterface               $optionSaver
     * @param AttributeRepositoryInterface $attributeRepository
     * @param GroupRepositoryInterface     $groupRepository
     * @param array                        $measuresConfig
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
        HandlerInterface $attributeHandler,
        Form $attributeForm,
        AttributeManager $attributeManager,
        AttributeOptionManager $optionManager,
        LocaleRepositoryInterface $localeRepository,
        VersionManager $versionManager,
        BulkSaverInterface $attributeSaver,
        RemoverInterface $attributeRemover,
        SaverInterface $optionSaver,
        AttributeRepositoryInterface $attributeRepository,
        GroupRepositoryInterface $groupRepository,
        $measuresConfig
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

        $this->attributeHandler = $attributeHandler;
        $this->attributeForm    = $attributeForm;
        $this->attributeManager = $attributeManager;
        $this->optionManager    = $optionManager;
        $this->localeRepository = $localeRepository;
        $this->versionManager   = $versionManager;
        $this->measuresConfig   = $measuresConfig;
        $this->attributeSaver   = $attributeSaver;
        $this->attributeRemover = $attributeRemover;
        $this->optionSaver      = $optionSaver;
        $this->attributeRepository = $attributeRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * List attributes
     *
     * @Template
     * @AclAncestor("pim_enrich_attribute_index")
     *
     * @return Template
     */
    public function indexAction()
    {
        return ['attributeTypes' => $this->attributeManager->getAttributeTypes()];
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
        $attributeTypes = $this->attributeManager->getAttributeTypes();

        if (!$attributeType || !is_string($attributeType) || !array_key_exists($attributeType, $attributeTypes)) {
            return $this->redirectToRoute('pim_enrich_attribute_index');
        }

        $attribute = $this->attributeManager->createAttribute($attributeType);

        if ($this->attributeHandler->process($attribute)) {
            $this->addFlash('success', 'flash.attribute.created');

            return new JsonResponse([
                'route'  => 'pim_enrich_attribute_edit',
                'params' => ['id' => $attribute->getId()]
            ]);
        }

        return [
            'form'            => $this->attributeForm->createView(),
            'locales'         => $this->localeRepository->getActivatedLocaleCodes(),
            'disabledLocales' => $this->localeRepository->findBy(['activated' => false]),
            'measures'        => $this->measuresConfig,
            'attributeType'   => $attributeType
        ];
    }

    /**
     * Edit attribute form
     *
     * @param Request $request
     * @param int     $id
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
            $this->addFlash('success', 'flash.attribute.updated');
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
     * Edit AttributeInterface sort order
     *
     * @param Request $request
     *
     * @AclAncestor("pim_enrich_attribute_sort")
     *
     * @return Response
     */
    public function sortAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_enrich_attribute_index');
        }

        $data = $request->request->all();

        if (!empty($data)) {
            $attributes = [];
            foreach ($data as $id => $sort) {
                $attribute = $this->attributeRepository->find((int) $id);
                if ($attribute) {
                    $attribute->setSortOrder((int) $sort);
                    $attributes[] = $attribute;
                }
            }
            $this->attributeSaver->saveAll($attributes);

            return new Response(1);
        }

        return new Response(0);
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
        if (!$request->isXmlHttpRequest() || !in_array($attribute->getAttributeType(), $this->choiceAttributeTypes)) {
            return $this->redirectToRoute('pim_enrich_attribute_edit', ['id' => $attribute->getId()]);
        }

        $option = $this->optionManager->createAttributeOption();

        $optionValue = $this->optionManager->createAttributeOptionValue();
        $optionValue->setLocale($dataLocale);
        $optionValue->setValue('');
        $option->addOptionValue($optionValue);

        $attribute->addOption($option);

        $form = $this->createForm('pim_attribute_option_create', $option);

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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function removeAction(Request $request, $id)
    {
        $attribute = $this->findAttributeOr404($id);
        $this->validateRemoval($attribute);

        $this->attributeRemover->remove($attribute);

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_attribute_index');
        }
    }

    /**
     * Find an attribute
     *
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return AttributeInterface
     */
    protected function findAttributeOr404($id)
    {
        return $this->findOr404($this->attributeManager->getAttributeClass(), $id);
    }

    /**
     * Check if the attribute is removable, otherwise throw an exception or redirect
     *
     * @param AttributeInterface $attribute
     *
     * @throws DeleteException For ajax requests if the attribute is not removable
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|null
     */
    protected function validateRemoval(AttributeInterface $attribute)
    {
        if (AttributeTypes::IDENTIFIER === $attribute->getAttributeType()) {
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
            if ($this->getRequest()->isXmlHttpRequest()) {
                throw new DeleteException($this->getTranslator()->trans($errorMessage, $messageParameters));
            } else {
                $this->addFlash($errorMessage, $messageParameters);

                return $this->redirectToRoute('pim_enrich_attribute_index');
            }
        }
    }
}
