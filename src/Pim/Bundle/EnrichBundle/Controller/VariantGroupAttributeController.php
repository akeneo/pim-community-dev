<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilderInterface;
use Pim\Bundle\CatalogBundle\Manager\VariantGroupAttributesResolver;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Enrich\Model\AvailableAttributes;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Variant group attribute controller, allows to add and remove optional attributes to a variant group
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupAttributeController
{
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
     * @param FormFactoryInterface            $formFactory
     * @param GroupRepositoryInterface        $groupRepository
     * @param SaverInterface                  $groupSaver
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param ProductTemplateBuilderInterface $templateBuilder
     * @param VariantGroupAttributesResolver  $groupAttrResolver
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        GroupRepositoryInterface $groupRepository,
        SaverInterface $groupSaver,
        AttributeRepositoryInterface $attributeRepository,
        ProductTemplateBuilderInterface $templateBuilder,
        VariantGroupAttributesResolver $groupAttrResolver
    ) {
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
     * @return JsonResponse
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

        return new JsonResponse(['route' => 'pim_enrich_variant_group_edit', 'params' => ['id' => $id]]);
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
     * @return JsonResponse
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

        return new JsonResponse(['route' => 'pim_enrich_variant_group_edit', 'params' => ['id' => $groupId]]);
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
     * @return \Pim\Component\Catalog\Model\AttributeInterface
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
     * Add flash message
     *
     * @param Request $request    the request
     * @param string  $type       the flash type
     * @param string  $message    the flash message
     * @param array   $parameters the flash message parameters
     */
    protected function addFlash(Request $request, $type, $message, array $parameters = [])
    {
        $request->getSession()->getFlashBag()->add($type, new Message($message, $parameters));
    }
}
