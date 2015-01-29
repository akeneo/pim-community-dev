<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Factory\ProductTemplateFactory;
use Pim\Bundle\CatalogBundle\Manager\ProductTemplateAttributesManager;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Saver\GroupSaver;
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

    /** @var GroupRepository */
    protected $groupRepository;

    /** @var GroupSaver */
    protected $groupSaver;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var ProductTemplateFactory */
    protected $templateFactory;

    /** @var ProductTemplateAttributesManager */
    protected $tplAttributesManager;

    /**
     * @param RouterInterface                  $router
     * @param FormFactoryInterface             $formFactory
     * @param GroupRepository                  $groupRepository
     * @param GroupSaver                       $groupSaver
     * @param AttributeRepository              $attributeRepository
     * @param ProductTemplateFactory           $templateFactory
     * @param ProductTemplateAttributesManager $tplAttributesManager
     */
    public function __construct(
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        GroupRepository $groupRepository,
        GroupSaver $groupSaver,
        AttributeRepository $attributeRepository,
        ProductTemplateFactory $templateFactory,
        ProductTemplateAttributesManager $tplAttributesManager
    ) {
        $this->router               = $router;
        $this->formFactory          = $formFactory;
        $this->groupRepository      = $groupRepository;
        $this->groupSaver           = $groupSaver;
        $this->attributeRepository  = $attributeRepository;
        $this->templateFactory      = $templateFactory;
        $this->tplAttributesManager = $tplAttributesManager;
    }

    /**
     * Add attributes to variant group
     *
     * @param Request $request
     * @param integer $id
     *
     * @AclAncestor("pim_enrich_group_add_attribute")
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
            $template = $this->templateFactory->createProductTemplate();
            $group->setProductTemplate($template);
        }

        $this->tplAttributesManager->addAttributes($template, $availableAttributes->getAttributes());
        $this->groupSaver->save($group, ['copy_values_to_products' => false]);
        $this->addFlash($request, 'success', 'flash.variant group.attributes_added');

        return $this->redirectToRoute('pim_enrich_variant_group_edit', ['id' => $id]);
    }

    /**
     * Remove an attribute form a variant group
     *
     * @param Request $request
     * @param integer $groupId
     * @param integer $attributeId
     *
     * @AclAncestor("pim_enrich_group_remove_attribute")
     * @return RedirectResponse
     *
     * @throws NotFoundHttpException
     */
    public function removeAttributeAction(Request $request, $groupId, $attributeId)
    {
        $group     = $this->findVariantGroupOr404($groupId);
        $attribute = $this->findAttributeOr404($attributeId);

        $template = $group->getProductTemplate();
        if (null !== $template) {
            $this->tplAttributesManager->removeAttribute($template, $attribute);
            $this->groupSaver->save($group);
        }

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        // TODO (JJ) useless else
        } else {
            return $this->redirectToRoute('pim_enrich_variant_group_edit', ['id' => $groupId]);
        }
    }

    /**
     * Find a variant group by its id or return a 404 response
     *
     * @param integer $id
     *
     * @return GroupInterface
     *
     * @throws NotFoundHttpException
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
     * @param integer $id
     *
     * @return Pim\Bundle\CatalogBundle\Model\AttributeInterface
     *
     * @throws NotFoundHttpException
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
        // TODO (JJ) getAxisAttributes
        $attributes = $group->getAttributes()->toArray();

        // TODO (JJ) the code that retrieves possible values for a variant should be extracted in a business service
        $template = $group->getProductTemplate();
        if (null !== $template) {
            foreach (array_keys($template->getValuesData()) as $attributeCode) {
                // TODO (JJ) don't use Doctrine's magic calls, use findOneBy instead
                // TODO (JJ) when repositories' PR is merged, ->findOneByIdentifier
                $attributes[] = $this->attributeRepository->findOneByCode($attributeCode);
            }
        }

        $uniqueAttributes = $this->attributeRepository->findBy(['unique' => true]);
        foreach ($uniqueAttributes as $attribute) {
            if (!in_array($attribute, $attributes)) {
                $attributes[] = $attribute;
            }
        }

        return $this->formFactory->create(
            'pim_available_attributes',
            $availableAttributes,
            ['attributes' => $attributes]
        );
    }

    /**
     * Create a redirection to a given route
     *
     * @param string  $route
     * @param mixed   $parameters
     * @param integer $status
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
     * @param string  $url    The URL to redirect to
     * @param integer $status The status code to use for the Response
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
     * @param string         $route         The name of the route
     * @param mixed          $parameters    An array of parameters
     * @param boolean|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
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
