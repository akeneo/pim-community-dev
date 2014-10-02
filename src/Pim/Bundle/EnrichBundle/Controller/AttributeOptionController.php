<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\View\View as RestVIew;
use FOS\RestBundle\View\ViewHandlerInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Attribute option controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionController
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var EntityManager */
    protected $entityManager;

    /** @var FormFactoryInterface */
    protected $formFactory;

    /** @var ViewHandlerInterface */
    protected $viewHandler;

    /** @var AttributeManager */
    protected $attributeManager;

    /**
     * Constructor
     *
     * @param NormalizerInterface  $normalizer
     * @param EntityManager        $entityManager
     * @param FormFactoryInterface $formFactory
     * @param ViewHandlerInterface $viewHandler
     * @param AttributeManager $attributeManager
     */
    public function __construct(
        NormalizerInterface $normalizer,
        EntityManager $entityManager,
        FormFactoryInterface $formFactory,
        ViewHandlerInterface $viewHandler,
        AttributeManager $attributeManager
    ) {
        $this->normalizer       = $normalizer;
        $this->entityManager    = $entityManager;
        $this->formFactory      = $formFactory;
        $this->viewHandler      = $viewHandler;
        $this->attributeManager = $attributeManager;
    }

    /**
     * Get all options of an attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return JsonResponse
     *
     * @ParamConverter("attribute", class="PimCatalogBundle:Attribute", options={"id" = "attribute_id"})
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function indexAction(AbstractAttribute $attribute)
    {
        $options = $this->normalizer->normalize($attribute->getOptions(), 'array', ['onlyActivatedLocales' => true]);

        return new JsonResponse($options);
    }

    /**
     * Create an option of an attribute
     *
     * @param Request           $request
     * @param AbstractAttribute $attribute
     *
     * @return JsonResponse
     *
     * @ParamConverter("attribute", class="PimCatalogBundle:Attribute", options={"id" = "attribute_id"})
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function createAction(Request $request, AbstractAttribute $attribute)
    {
        $attributeOption = $this->attributeManager->createAttributeOption();
        $attributeOption->setAttribute($attribute);

        //Should be replaced by a paramConverter
        $data = json_decode($request->getContent(), true);

        return $this->manageFormSubmission($attributeOption, $data);
    }

    /**
     * Update an option of an attribute
     *
     * @param Request           $request
     * @param AbstractAttribute $attribute
     * @param AttributeOption   $attributeOption
     *
     * @return JsonResponse
     *
     * @ParamConverter("attribute", class="PimCatalogBundle:Attribute", options={"id" = "attribute_id"})
     * @ParamConverter("attributeOption", class="PimCatalogBundle:AttributeOption", options={"id" = "option_id"})
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function updateAction(Request $request, AbstractAttribute $attribute, AttributeOption $attributeOption)
    {
        //Should be replaced by a paramConverter
        $data = json_decode($request->getContent(), true);

        return $this->manageFormSubmission($attributeOption, $data);
    }

    /**
     * Delete an option of an attribute
     *
     * @param AbstractAttribute $attribute
     * @param AttributeOption   $attributeOption
     *
     * @return JsonResponse
     *
     * @ParamConverter("attribute", class="PimCatalogBundle:Attribute", options={"id" = "attribute_id"})
     * @ParamConverter("attributeOption", class="PimCatalogBundle:AttributeOption", options={"id" = "option_id"})
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function deleteAction(AbstractAttribute $attribute, AttributeOption $attributeOption)
    {
        $this->entityManager->remove($attributeOption);
        $this->entityManager->flush($attributeOption);

        return new JsonResponse();
    }

    /**
     * Update sorting of the options
     *
     * @param Request           $request
     * @param AbstractAttribute $attribute
     *
     * @return JsonResponse
     *
     * @ParamConverter("attribute", class="PimCatalogBundle:Attribute", options={"id" = "attribute_id"})
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function updateSortingAction(Request $request, AbstractAttribute $attribute)
    {
        //Should be replaced by a paramConverter
        $sorting = array_flip(json_decode($request->getContent(), true));

        foreach ($attribute->getOptions() as $option) {
            $option->setSortOrder($sorting[$option->getId()]);

            $this->entityManager->persist($option);
        }

        $this->entityManager->flush();

        return new JsonResponse();
    }

    /**
     * Manage form submission of an attribute option
     * @param AttributeOption $attributeOption
     * @param array           $data
     *
     * @return FormInterface
     */
    protected function manageFormSubmission(AttributeOption $attributeOption, $data = [])
    {
        $form = $this->formFactory->createNamed('option', 'pim_enrich_attribute_option', $attributeOption);

        $form->submit($data, false);

        if ($form->isValid()) {
            $this->entityManager->persist($attributeOption);
            $this->entityManager->flush();

            $option = $this->normalizer->normalize($attributeOption, 'array', ['onlyActivatedLocales' => true]);

            return new JsonResponse($option);
        }

        return $this->viewHandler->handle(RestView::create($form));
    }
}
