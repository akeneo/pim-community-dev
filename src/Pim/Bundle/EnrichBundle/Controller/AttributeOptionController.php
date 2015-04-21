<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use FOS\RestBundle\View\View as RestView;
use FOS\RestBundle\View\ViewHandlerInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\Manager\AttributeOptionManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeOptionInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    /** @var AttributeOptionManager */
    protected $optionManager;

    /** @var RemoverInterface */
    protected $optionRemover;

    /** @var SaverInterface */
    protected $optionSaver;

    /**
     * Constructor
     *
     * @param NormalizerInterface    $normalizer
     * @param EntityManager          $entityManager
     * @param FormFactoryInterface   $formFactory
     * @param ViewHandlerInterface   $viewHandler
     * @param AttributeManager       $attributeManager
     * @param AttributeOptionManager $optionManager
     * @param SaverInterface         $optionSaver
     * @param RemoverInterface       $optionRemover
     */
    public function __construct(
        NormalizerInterface $normalizer,
        EntityManager $entityManager,
        FormFactoryInterface $formFactory,
        ViewHandlerInterface $viewHandler,
        AttributeManager $attributeManager,
        AttributeOptionManager $optionManager,
        SaverInterface $optionSaver,
        RemoverInterface $optionRemover
    ) {
        $this->normalizer       = $normalizer;
        $this->entityManager    = $entityManager;
        $this->formFactory      = $formFactory;
        $this->viewHandler      = $viewHandler;
        $this->attributeManager = $attributeManager;
        $this->optionManager    = $optionManager;
        $this->optionRemover    = $optionRemover;
        $this->optionSaver      = $optionSaver;
    }

    /**
     * Get all options of an attribute
     *
     * @param int $attributeId
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function indexAction($attributeId)
    {
        $attribute = $this->findAttributeOr404($attributeId);

        $options = $this->normalizer->normalize($attribute->getOptions(), 'array', ['onlyActivatedLocales' => true]);

        return new JsonResponse($options);
    }

    /**
     * Create an option of an attribute
     *
     * @param Request $request
     * @param int     $attributeId
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function createAction(Request $request, $attributeId)
    {
        $attribute = $this->findAttributeOr404($attributeId);

        $attributeOption = $this->optionManager->createAttributeOption();
        $attributeOption->setAttribute($attribute);

        //Should be replaced by a paramConverter
        $data = json_decode($request->getContent(), true);

        return $this->manageFormSubmission($attributeOption, $data);
    }

    /**
     * Update an option of an attribute
     *
     * @param Request $request
     * @param int     $attributeOptionId
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function updateAction(Request $request, $attributeOptionId)
    {
        $attributeOption = $this->findAttributeOptionOr404($attributeOptionId);

        //Should be replaced by a paramConverter
        $data = json_decode($request->getContent(), true);

        return $this->manageFormSubmission($attributeOption, $data);
    }

    /**
     * Delete an option of an attribute
     *
     * @param int $attributeOptionId
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function deleteAction($attributeOptionId)
    {
        $attributeOption = $this->findAttributeOptionOr404($attributeOptionId);

        try {
            $this->optionRemover->remove($attributeOption);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], $e->getCode());
        }

        return new JsonResponse();
    }

    /**
     * Update sorting of the options
     *
     * @param Request $request
     * @param int     $attributeId
     *
     * @return JsonResponse
     *
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function updateSortingAction(Request $request, $attributeId)
    {
        $attribute = $this->findAttributeOr404($attributeId);
        //Should be replaced by a paramConverter
        $data = json_decode($request->getContent(), true);

        $sorting = array_flip($data);

        $this->attributeManager->updateSorting($attribute, $sorting);

        return new JsonResponse();
    }

    /**
     * Manage form submission of an attribute option
     *
     * @param AttributeOptionInterface $attributeOption
     * @param array                    $data
     *
     * @return FormInterface
     */
    protected function manageFormSubmission(AttributeOptionInterface $attributeOption, array $data = [])
    {
        $form = $this->formFactory->createNamed('option', 'pim_enrich_attribute_option', $attributeOption);

        $form->submit($data, false);

        if ($form->isValid()) {
            $this->optionSaver->save($attributeOption);

            $option = $this->normalizer->normalize($attributeOption, 'array', ['onlyActivatedLocales' => true]);

            return new JsonResponse($option);
        }

        return $this->viewHandler->handle(RestView::create($form));
    }

    /**
     * Find an attribute or throw a 404
     *
     * @param int $id The id of the attribute
     *
     * @throws NotFoundHttpException
     *
     * @return AttributeInterface
     */
    protected function findAttributeOr404($id)
    {
        try {
            $result = $this->attributeManager->getAttribute($id);
        } catch (EntityNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $result;
    }

    /**
     * Find an attribute option or throw a 404
     *
     * @param int $id The id of the attribute option
     *
     * @throws NotFoundHttpException
     *
     * @return AttributeOptionInterface
     */
    protected function findAttributeOptionOr404($id)
    {
        try {
            $result = $this->optionManager->getAttributeOption($id);
        } catch (EntityNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $result;
    }
}
