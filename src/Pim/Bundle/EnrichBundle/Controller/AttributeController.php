<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\EnrichBundle\Form\Handler\AttributeHandler;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
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
 * Attribute controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeController extends AbstractDoctrineController
{
    /**
     * @var AttributeHandler
     */
    protected $attributeHandler;

    /**
     * @var Form
     */
    protected $attributeForm;

    /**
     * @var AttributeManager
     */
    protected $attributeManager;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var VersionManager
     */
    protected $versionManager;

    /**
     * @var array
     */
    protected $measuresConfig;

    /**
     * @var array
     */
    protected $choiceAttributeTypes = array(
        'pim_catalog_simpleselect',
        'pim_catalog_multiselect'
    );

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
     * @param AttributeHandler         $attributeHandler
     * @param Form                     $attributeForm
     * @param AttributeManager         $attributeManager
     * @param LocaleManager            $localeManager
     * @param VersionManager           $versionManager
     * @param array                    $measuresConfig
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
        AttributeHandler $attributeHandler,
        Form $attributeForm,
        AttributeManager $attributeManager,
        LocaleManager $localeManager,
        VersionManager $versionManager,
        $measuresConfig
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

        $this->attributeHandler = $attributeHandler;
        $this->attributeForm    = $attributeForm;
        $this->attributeManager = $attributeManager;
        $this->localeManager    = $localeManager;
        $this->versionManager   = $versionManager;
        $this->measuresConfig   = $measuresConfig;
    }

    /**
     * List attributes
     *
     * @Template
     * @AclAncestor("pim_enrich_attribute_index")
     * @return Template
     */
    public function indexAction()
    {
        return ['attributeTypes' => $this->attributeManager->getAttributeTypes()];
    }

    /**
     * Create attribute
     * @param Request $request
     *
     * @Template("PimEnrichBundle:Attribute:form.html.twig")
     * @AclAncestor("pim_enrich_attribute_create")
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

            return $this->redirectToRoute('pim_enrich_attribute_edit', ['id' => $attribute->getId()]);
        }

        return [
            'form'            => $this->attributeForm->createView(),
            'locales'         => $this->localeManager->getActiveLocales(),
            'disabledLocales' => $this->localeManager->getDisabledLocales(),
            'measures'        => $this->measuresConfig,
            'attributeType'   => $attributeType
        ];
    }

    /**
     * Edit attribute form
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template("PimEnrichBundle:Attribute:form.html.twig")
     * @AclAncestor("pim_enrich_attribute_edit")
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $attribute = $this->findAttributeOr404($id);
        if ($this->attributeHandler->process($attribute)) {
            $this->addFlash('success', 'flash.attribute.updated');

            return $this->redirectToRoute('pim_enrich_attribute_edit', array('id' => $attribute->getId()));
        }

        return array(
            'form'            => $this->attributeForm->createView(),
            'locales'         => $this->localeManager->getActiveLocales(),
            'disabledLocales' => $this->localeManager->getDisabledLocales(),
            'measures'        => $this->measuresConfig,
            'created'         => $this->versionManager->getOldestLogEntry($attribute),
            'updated'         => $this->versionManager->getNewestLogEntry($attribute),
        );
    }

    /**
     * Edit AttributeInterface sort order
     *
     * @param Request $request
     *
     * @AclAncestor("pim_enrich_attribute_sort")
     * @return Response
     */
    public function sortAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_enrich_attribute_index');
        }

        $data = $request->request->all();

        if (!empty($data)) {
            foreach ($data as $id => $sort) {
                $attribute = $this->getRepository($this->attributeManager->getAttributeClass())->find((int) $id);
                if ($attribute) {
                    $attribute->setSortOrder((int) $sort);
                    $this->persist($attribute, false);
                }
            }
            $this->getManagerForClass($this->attributeManager->getAttributeClass())->flush();

            return new Response(1);
        }

        return new Response(0);
    }

    /**
     * Create a new option for a simple/multi-select attribute
     *
     * @param Request $request
     * @param integer $id
     * @param string  $dataLocale
     *
     * @Template("PimEnrichBundle:Attribute:form_options.html.twig")
     * @AclAncestor("pim_enrich_attribute_edit")
     * @return Response
     */
    public function createOptionAction(Request $request, $id, $dataLocale)
    {
        $attribute = $this->findAttributeOr404($id);
        if (!$request->isXmlHttpRequest() || !in_array($attribute->getAttributeType(), $this->choiceAttributeTypes)) {
            return $this->redirectToRoute('pim_enrich_attribute_edit', array('id'=> $attribute->getId()));
        }

        $option = $this->attributeManager->createAttributeOption();

        $optionValue = $this->attributeManager->createAttributeOptionValue();
        $optionValue->setLocale($dataLocale);
        $optionValue->setValue('');
        $option->addOptionValue($optionValue);

        $attribute->addOption($option);

        $form = $this->createForm('pim_attribute_option_create', $option);

        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->persist($option);
                $response = array(
                    'status' => 1,
                    'option' => array(
                        'id'    => $option->getId(),
                        'label' => $option->setLocale($dataLocale)->__toString()
                    )
                );

                return new Response(json_encode($response));
            }
        }

        return array(
            'attribute' => $attribute,
            'form'      => $form->createView()
        );
    }

    /**
     * Remove attribute
     *
     * @param Request $request
     * @param integer $id
     *
     * @AclAncestor("pim_enrich_attribute_remove")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function removeAction(Request $request, $id)
    {
        $attribute = $this->findAttributeOr404($id);
        $this->validateRemoval($attribute);

        $this->attributeManager->remove($attribute);

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_enrich_attribute_index');
        }
    }

    /**
     * Find an attribute
     *
     * @param integer $id
     *
     * @return AttributeInterface
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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
     * @return RedirectResponse|null
     */
    protected function validateRemoval(AttributeInterface $attribute)
    {
        if ($attribute->getAttributeType() === 'pim_catalog_identifier') {
            $errorMessage = 'flash.attribute.identifier not removable';
            $messageParameters = array();
        } else {
            $groupCount = $this->getRepository('Pim\Bundle\CatalogBundle\Entity\Group')
                ->countVariantGroupAxis($attribute);
            if ($groupCount > 0) {
                $errorMessage = 'flash.attribute.used by groups';
                $messageParameters = array('%count%' => $groupCount);
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
