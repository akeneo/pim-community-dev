<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use FOS\RestBundle\View\View as RestVIew;
use FOS\RestBundle\View\ViewHandlerInterface;

/**
 * Attribute option controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionController
{
    protected $serializer;

    /**
     * Constructor
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(
        SerializerInterface $serializer,
        EntityManager $entityManager,
        FormFactoryInterface $formFactory,
        ViewHandlerInterface $viewHandler
    ) {
        $this->serializer    = $serializer;
        $this->entityManager = $entityManager;
        $this->formFactory   = $formFactory;
        $this->viewHandler   = $viewHandler;
    }

    /**
     * Get all options of an attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return AttributeOption[]
     *
     * @ParamConverter("attribute", class="PimCatalogBundle:Attribute", options={"id" = "attribute_id"})
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function indexAction(AbstractAttribute $attribute)
    {
        $options = $this->serializer->normalize($attribute->getOptions(), 'array', ['onlyActivatedLocales' => true]);

        return new JsonResponse($options);
    }

    /**
     * Update an option of an attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return AttributeOption[]
     *
     * @ParamConverter("attribute", class="PimCatalogBundle:Attribute", options={"id" = "attribute_id"})
     * @ParamConverter("attributeOption", class="PimCatalogBundle:AttributeOption", options={"id" = "option_id"})
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function updateAction(Request $request, AbstractAttribute $attribute, AttributeOption $attributeOption)
    {
        $form = $this->formFactory->createNamed('option', 'pim_enrich_attribute_option', $attributeOption);

        //Should be replaced by a paramConverter
        $data = json_decode($request->getContent(), true);

        $form->submit($data, false);

        if ($form->isValid()) {
            $this->entityManager->persist($attributeOption);
            $this->entityManager->flush($attributeOption);
        }

        return $this->viewHandler->handle(RestView::create($form));
    }

    /**
     * Delete an option of an attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return AttributeOption[]
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
}
