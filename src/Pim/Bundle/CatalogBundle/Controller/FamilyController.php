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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\GridBundle\Renderer\GridRenderer;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Pim\Bundle\CatalogBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\CatalogBundle\Datagrid\DatagridWorkerInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\AvailableProductAttributes;
use Pim\Bundle\CatalogBundle\Form\Type\AvailableProductAttributesType;
use Symfony\Component\HttpFoundation\Response;
use Pim\Bundle\CatalogBundle\Exception\DeleteException;

/**
 * Family controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyController extends AbstractDoctrineController
{
    /**
     * @var GridRenderer
     */
    private $gridRenderer;

    /**
     * @var DatagridWorkerInterface
     */
    private $dataGridWorker;

    /**
     * @var ChannelManager
     */
    private $channelManager;

    /**
     * @var ProductManager
     */
    private $productManager;

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
     * @param GridRenderer             $gridRenderer
     * @param DatagridWorkerInterface  $dataGridWorker
     * @param ChannelManager           $channelManager
     * @param ProductManager           $productManager
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
        GridRenderer $gridRenderer,
        DatagridWorkerInterface $dataGridWorker,
        ChannelManager $channelManager,
        ProductManager $productManager
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

        $this->gridRenderer   = $gridRenderer;
        $this->dataGridWorker = $dataGridWorker;
        $this->channelManager = $channelManager;
        $this->productManager = $productManager;
    }

    /**
     * Create a family
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_catalog_family_create")
     * @return array
     */
    public function createAction(Request $request)
    {
        $family   = new Family();
        $families = $this->getRepository('PimCatalogBundle:Family')->getIdToLabelOrderedByLabel();

        $form = $this->createForm('pim_family', $family);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $identifier = $this->productManager->getIdentifierAttribute();
                $family->addAttribute($identifier);
                $this->getManager()->persist($family);
                $this->getManager()->flush();
                $this->addFlash('success', 'flash.family.created');

                return $this->redirectToRoute('pim_catalog_family_edit', array('id' => $family->getId()));
            }
        }

        return array(
            'form'     => $form->createView(),
            'families' => $families,
        );
    }

    /**
     * Edit a family
     *
     * @param Request $request
     * @param integer $id
     *
     * @Template
     * @AclAncestor("pim_catalog_family_edit")
     * @return array
     */
    public function editAction(Request $request, $id)
    {
        $family   = $this->findOr404('PimCatalogBundle:Family', $id);
        $datagrid = $this->dataGridWorker->getDataAuditDatagrid(
            $family,
            'pim_catalog_family_edit',
            array('id' => $family->getId())
        );
        $datagridView = $datagrid->createView();

        if ('json' === $request->getRequestFormat()) {
            return $this->gridRenderer->renderResultsJsonResponse($datagridView);
        }

        $families = $this->getRepository('PimCatalogBundle:Family')->getIdToLabelOrderedByLabel();
        $channels = $this->channelManager->getChannels();
        $form = $this->createForm(
            'pim_family',
            $family,
            array(
                'channels'   => $channels,
                'attributes' => $family->getAttributes(),
            )
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getManager()->flush();
                $this->addFlash('success', 'flash.family.updated');

                return $this->redirectToRoute('pim_catalog_family_edit', array('id' => $id));
            }
        }

        return array(
            'family'         => $family,
            'families'       => $families,
            'channels'       => $channels,
            'form'           => $form->createView(),
            'datagrid'       => $datagridView,
            'attributesForm' => $this->getAvailableProductAttributesForm(
                $family->getAttributes()->toArray()
            )->createView(),
        );
    }

    /**
     * Remove a family
     *
     * @param Family $entity
     *
     * @AclAncestor("pim_catalog_family_remove")
     * @return Response|RedirectResponse
     */
    public function removeAction(Family $entity)
    {
        $this->getManager()->remove($entity);
        $this->getManager()->flush();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_family_create');
        }
    }

    /**
     * Add attributes to a family
     *
     * @param Request $request The request object
     * @param integer $id      The family id to which add attributes
     *
     * @AclAncestor("pim_catalog_family_add_attribute")
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addProductAttributesAction(Request $request, $id)
    {
        $family              = $this->findOr404('PimCatalogBundle:Family', $id);
        $availableAttributes = new AvailableProductAttributes();
        $attributesForm      = $this->getAvailableProductAttributesForm(
            $family->getAttributes()->toArray(),
            $availableAttributes
        );

        $attributesForm->bind($request);

        foreach ($availableAttributes->getAttributes() as $attribute) {
            $family->addAttribute($attribute);
        }

        $this->getManager()->flush();

        $this->addFlash('success', 'flash.family.attributes added');

        return $this->redirectToRoute('pim_catalog_family_edit', array('id' => $family->getId()));
    }

    /**
     * Remove product attribute
     *
     * @param integer $familyId
     * @param integer $attributeId
     *
     * @AclAncestor("pim_catalog_family_remove_atribute")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws DeleteException
     */
    public function removeProductAttributeAction($familyId, $attributeId)
    {
        $family    = $this->findOr404('PimCatalogBundle:Family', $familyId);
        $attribute = $this->findOr404('PimCatalogBundle:ProductAttribute', $attributeId);

        if (false === $family->hasAttribute($attribute)) {
            throw new DeleteException($this->getTranslator()->trans('flash.family.attribute not found'));
        } elseif ($attribute->getAttributeType() === 'pim_catalog_identifier') {
            throw new DeleteException($this->getTranslator()->trans('flash.family.identifier not removable'));
        } elseif ($attribute === $family->getAttributeAsLabel()) {
            throw new DeleteException($this->getTranslator()->trans('flash.family.label attribute not removable'));
        } else {
            $family->removeAttribute($attribute);
            $this->getManager()->flush();
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_family_edit', array('id' => $family->getId()));
        }
    }

    /**
     * Get the AvailbleProductAttributes form
     *
     * @param array                      $attributes          The product attributes
     * @param AvailableProductAttributes $availableAttributes The available attributes container
     *
     * @return Symfony\Component\Form\Form
     */
    protected function getAvailableProductAttributesForm(
        array $attributes = array(),
        AvailableProductAttributes $availableAttributes = null
    ) {
        return $this->createForm(
            new AvailableProductAttributesType(),
            $availableAttributes ?: new AvailableProductAttributes(),
            array('attributes' => $attributes)
        );
    }
}
