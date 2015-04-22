<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilderInterface;
use Pim\Bundle\CatalogBundle\Manager\VariantGroupAttributesResolver;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Variant group attribute controller, allows to add and remove optional attributes to a variant group
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupAttributeController
{
    /** @var RouterInterface */
    protected $router;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var SaverInterface */
    protected $groupSaver;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductTemplateBuilderInterface */
    protected $templateBuilder;

    /** @var VariantGroupAttributesResolver */
    protected $groupAttrResolver;

    /**
     * @param RouterInterface                 $router
     * @param FormFactoryInterface            $formFactory
     * @param GroupRepositoryInterface        $groupRepository
     * @param SaverInterface                  $groupSaver
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param ProductTemplateBuilderInterface $templateBuilder
     * @param VariantGroupAttributesResolver  $groupAttrResolver
     */
    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        GroupRepositoryInterface $groupRepository,
        SaverInterface $groupSaver,
        AttributeRepositoryInterface $attributeRepository,
        ProductTemplateBuilderInterface $templateBuilder,
        VariantGroupAttributesResolver $groupAttrResolver
    ) {
        $this->router              = $router;
        $this->formFactory         = $formFactory;
        $this->groupRepository     = $groupRepository;
        $this->groupSaver          = $groupSaver;
        $this->attributeRepository = $attributeRepository;
        $this->templateBuilder     = $templateBuilder;
        $this->groupAttrResolver   = $groupAttrResolver;
    }

    /**
     * Add attributes to variant group
     *
     * @param Request $request
     * @param int     $id
     *
     * @AclAncestor("pim_enrich_group_add_attribute")
     *
     * @return RedirectResponse
     */
    public function addAttributesAction(Request $request, $id)
    {
        $group               = $this->findVariantGroupOr404($id);
        $availableAttributes = new AvailableAttributes();
        $attributesForm      = $this->getAvailableAttributesForm($group, $availableAttributes);
        $attributesForm->submit($request);

        $template = $group->getProductTemplate();
        if (null === $template) {
            $template = $this->templateBuilder->createProductTemplate();
            $group->setProductTemplate($template);
        }

        $this->templateBuilder->addAttributes($template, $availableAttributes->getAttributes());
        $this->groupSaver->save($group, ['copy_values_to_products' => false]);
        $this->addFlash($request, 'success', 'flash.variant group.attributes_added');

        return $this->redirectToRoute('pim_enrich_variant_group_edit', ['id' => $id]);
    }

    /**
     * Remove an attribute form a variant group
     *
     * @param Request $request
     * @param int     $groupId
     * @param int     $attributeId
     *
     * @AclAncestor("pim_enrich_group_remove_attribute")
     *
     * @throws NotFoundHttpException
     *
     * @return RedirectResponse
     */
    public function removeAttributeAction(Request $request, $groupId, $attributeId)
    {
        $group     = $this->findVariantGroupOr404($groupId);
        $attribute = $this->findAttributeOr404($attributeId);

        $template = $group->getProductTemplate();
        if (null !== $template) {
            $this->templateBuilder->removeAttribute($template, $attribute);
            $this->groupSaver->save($group);
        }

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        }

        return $this->redirectToRoute('pim_enrich_variant_group_edit', ['id' => $groupId]);
    }

    /**
     * Find a variant group by its id or return a 404 response
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     *
     * @return GroupInterface
     */
    protected function findVariantGroupOr404($id)
    {
        $group = $this->groupRepository->find($id);

        if (!$group || !$group->getType()->isVariant()) {
            throw new NotFoundHttpException(
                sprintf('Variant group with id %d could not be found.', $id)
            );
        }

        return $group;
    }

    /**
     * Find an attribute by its id or return a 404 response
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     *
     * @return \Pim\Bundle\CatalogBundle\Model\AttributeInterface
     */
    protected function findAttributeOr404($id)
    {
        $attribute = $this->attributeRepository->find($id);

        if (!$attribute) {
            throw new NotFoundHttpException(
                sprintf('Attribute with id %s could not be found.', $id)
            );
        }

        return $attribute;
    }

    /**
     * Get the AvailableAttributes form
     *
     * @param GroupInterface      $group
     * @param AvailableAttributes $availableAttributes
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function getAvailableAttributesForm(GroupInterface $group, AvailableAttributes $availableAttributes)
    {
        return $this->formFactory->create(
            'pim_available_attributes',
            $availableAttributes,
            ['excluded_attributes' => $this->groupAttrResolver->getNonEligibleAttributes($group)]
        );
    }

    /**
     * Create a redirection to a given route
     *
     * @param string $route
     * @param mixed  $parameters
     * @param int    $status
     *
     * @return RedirectResponse
     */
    protected function redirectToRoute($route, $parameters = array(), $status = 302)
    {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string $url    The URL to redirect to
     * @param int    $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string      $route         The name of the route
     * @param mixed       $parameters    An array of parameters
     * @param bool|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    protected function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->router->generate($route, $parameters, $referenceType);
    }

    /**
     * Add flash message
     *
     * @param Request $request    the request
     * @param string  $type       the flash type
     * @param string  $message    the flash message
     * @param array   $parameters the flash message parameters
     */
    protected function addFlash(Request $request, $type, $message, array $parameters = array())
    {
        $request->getSession()->getFlashBag()->add($type, new Message($message, $parameters));
    }
}
