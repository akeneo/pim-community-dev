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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Form;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Form\Handler\AttributeHandler;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Exception\DeleteException;
use Pim\Bundle\CatalogBundle\Manager\AttributeManagerInterface;
use Pim\Bundle\VersioningBundle\Manager\AuditManager;

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
     * @var ProductManager
     */
    protected $attributeManager;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var AuditManager
     */
    protected $auditManager;

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
     * @param Request                   $request
     * @param EngineInterface           $templating
     * @param RouterInterface           $router
     * @param SecurityContextInterface  $securityContext
     * @param FormFactoryInterface      $formFactory
     * @param ValidatorInterface        $validator
     * @param TranslatorInterface       $translator
     * @param RegistryInterface         $doctrine
     * @param AttributeHandler          $attributeHandler
     * @param Form                      $attributeForm
     * @param AttributeManagerInterface $attributeManager
     * @param LocaleManager             $localeManager
     * @param AuditManager              $auditManager
     * @param array                     $measuresConfig
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
        AttributeHandler $attributeHandler,
        Form $attributeForm,
        AttributeManagerInterface $attributeManager,
        LocaleManager $localeManager,
        AuditManager $auditManager,
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
            $doctrine
        );

        $this->attributeHandler = $attributeHandler;
        $this->attributeForm    = $attributeForm;
        $this->attributeManager = $attributeManager;
        $this->localeManager    = $localeManager;
        $this->auditManager     = $auditManager;
        $this->measuresConfig   = $measuresConfig;
    }
    /**
     * List attributes
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_attribute_index")
     * @return template
     */
    public function indexAction(Request $request)
    {
        return array(
            'localeCode' => $this->localeManager->getUserLocale()->getCode()
        );
    }

    /**
     * Create attribute
     *
     * @Template("PimCatalogBundle:Attribute:form.html.twig")
     * @AclAncestor("pim_catalog_attribute_create")
     * @return array
     */
    public function createAction()
    {
        $attribute = $this->attributeManager->createAttribute('pim_catalog_text');

        if ($this->attributeHandler->process($attribute)) {
            $this->addFlash('success', 'flash.attribute.created');

            return $this->redirectToRoute('pim_catalog_attribute_edit', array('id' => $attribute->getId()));
        }

        return array(
            'form'            => $this->attributeForm->createView(),
            'locales'         => $this->localeManager->getActiveLocales(),
            'disabledLocales' => $this->localeManager->getDisabledLocales(),
            'measures'        => $this->measuresConfig
        );
    }

    /**
     * Edit attribute form
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template("PimCatalogBundle:Attribute:form.html.twig")
     * @AclAncestor("pim_catalog_attribute_edit")
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $attribute = $this->findAttributeOr404($id);
        if ($this->attributeHandler->process($attribute)) {
            $this->addFlash('success', 'flash.attribute.updated');

            return $this->redirectToRoute('pim_catalog_attribute_edit', array('id' => $attribute->getId()));
        }

        return array(
            'form'            => $this->attributeForm->createView(),
            'locales'         => $this->localeManager->getActiveLocales(),
            'disabledLocales' => $this->localeManager->getDisabledLocales(),
            'measures'        => $this->measuresConfig,
            'created'         => $this->auditManager->getOldestLogEntry($attribute),
            'updated'         => $this->auditManager->getNewestLogEntry($attribute),
        );
    }

    /**
     * Preprocess attribute form
     *
     * @param Request $request
     *
     * @Template("PimCatalogBundle:Attribute:_form_parameters.html.twig")
     * @AclAncestor("pim_catalog_attribute_edit")
     * @return array
     */
    public function preProcessAction(Request $request)
    {
        $data = $request->request->all();
        if (!isset($data['pim_catalog_attribute_form'])) {
            return $this->redirectToRoute('pim_catalog_attribute_create');
        }

        // Add custom fields to the form and set the entered data to the form
        $this->attributeHandler->preProcess($data['pim_catalog_attribute_form']);

        $locales         = $this->localeManager->getActiveLocales();
        $disabledLocales = $this->localeManager->getDisabledLocales();
        $form            = $this->attributeForm->createView();

        $data = array(
            'parameters' => $this->renderView(
                'PimCatalogBundle:Attribute:_form_parameters.html.twig',
                array(
                    'form'            => $form,
                    'locales'         => $locales,
                    'disabledLocales' => $disabledLocales
                )
            ),
            'values' => $this->renderView(
                'PimCatalogBundle:Attribute:_form_values.html.twig',
                array(
                    'form'            => $form,
                    'locales'         => $locales,
                    'disabledLocales' => $disabledLocales
                )
            )
        );

        return new JsonResponse($data);
    }

    /**
     * Edit AttributeInterface sort order
     *
     * @param Request $request
     *
     * @AclAncestor("pim_catalog_attribute_sort")
     * @return Response
     */
    public function sortAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_catalog_attribute_index');
        }

        $data = $request->request->all();

        if (!empty($data)) {
            foreach ($data as $id => $sort) {
                $attribute = $this->getRepository($this->attributeManager->getAttributeClass())->find((int) $id);
                if ($attribute) {
                    $attribute->setSortOrder((int) $sort);
                    $this->getManager()->persist($attribute);
                }
            }
            $this->getManager()->flush();

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
     * @Template("PimCatalogBundle:Attribute:form_options.html.twig")
     * @AclAncestor("pim_catalog_attribute_edit")
     * @return Response
     */
    public function createOptionAction(Request $request, $id, $dataLocale)
    {
        $attribute = $this->findAttributeOr404($id);
        if (!$request->isXmlHttpRequest() || !in_array($attribute->getAttributeType(), $this->choiceAttributeTypes)) {
            return $this->redirectToRoute('pim_catalog_attribute_edit', array('id'=> $attribute->getId()));
        }

        $option = new AttributeOption();

        $optionValue = new AttributeOptionValue();
        $optionValue->setLocale($dataLocale);
        $optionValue->setValue('');
        $option->addOptionValue($optionValue);

        $attribute->addOption($option);

        $form = $this->createForm('pim_attribute_option_create', $option);

        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->getManager()->persist($option);
                $this->getManager()->flush();
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
            'attribute'  => $attribute,
            'dataLocale' => $dataLocale,
            'form'       => $form->createView()
        );
    }

    /**
     * Remove attribute
     *
     * @param Request $request
     * @param integer $id
     *
     * @AclAncestor("pim_catalog_attribute_remove")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function removeAction(Request $request, $id)
    {
        $attribute = $this->findAttributeOr404($id);
        $this->validateRemoval($attribute);

        $this->getManager()->remove($attribute);
        $this->getManager()->flush();

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_attribute_index');
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
            $groupCount = $this->getRepository('Pim\Bundle\CatalogBundle\Entity\Group')->countForAttribute($attribute);
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

                return $this->redirectToRoute('pim_catalog_attribute_index');
            }
        }
    }
}
