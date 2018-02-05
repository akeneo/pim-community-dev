<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use FOS\RestBundle\View\View as RestView;
use FOS\RestBundle\View\ViewHandlerInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Component\Catalog\Manager\AttributeOptionsSorter;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Repository\AttributeOptionRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    /** @var AttributeOptionsSorter */
    protected $sorter;

    /** @var SimpleFactoryInterface */
    protected $optionFactory;

    /** @var RemoverInterface */
    protected $optionRemover;

    /** @var SaverInterface */
    protected $optionSaver;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var AttributeOptionRepositoryInterface */
    protected $optionRepository;

    /**
     * @param NormalizerInterface          $normalizer
     * @param EntityManager                $entityManager
     * @param FormFactoryInterface         $formFactory
     * @param ViewHandlerInterface         $viewHandler
     * @param AttributeOptionsSorter       $sorter
     * @param SimpleFactoryInterface       $optionFactory
     * @param SaverInterface               $optionSaver
     * @param RemoverInterface             $optionRemover
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AttributeOptionRepositoryInterface $attributeOptionRepository
     */
    public function __construct(
        NormalizerInterface $normalizer,
        EntityManager $entityManager,
        FormFactoryInterface $formFactory,
        ViewHandlerInterface $viewHandler,
        AttributeOptionsSorter $sorter,
        SimpleFactoryInterface $optionFactory,
        SaverInterface $optionSaver,
        RemoverInterface $optionRemover,
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionRepositoryInterface $attributeOptionRepository
    ) {
        $this->normalizer = $normalizer;
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
        $this->viewHandler = $viewHandler;
        $this->sorter = $sorter;
        $this->optionFactory = $optionFactory;
        $this->optionRemover = $optionRemover;
        $this->optionSaver = $optionSaver;
        $this->attributeRepository = $attributeRepository;
        $this->optionRepository = $attributeOptionRepository;
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
     * @return FormInterface|RedirectResponse
     *
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function createAction(Request $request, $attributeId)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $attribute = $this->findAttributeOr404($attributeId);

        $attributeOption = $this->optionFactory->create();
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
     * @return FormInterface|RedirectResponse
     *
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function updateAction(Request $request, $attributeOptionId)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

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
     * @return JsonResponse|RedirectResponse
     *
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function deleteAction(Request $request, $attributeOptionId)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

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
     * @return JsonResponse|RedirectResponse
     *
     * @AclAncestor("pim_enrich_attribute_edit")
     */
    public function updateSortingAction(Request $request, $attributeId)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $attribute = $this->findAttributeOr404($attributeId);
        //Should be replaced by a paramConverter
        $data = json_decode($request->getContent(), true);

        $sorting = array_flip($data);

        $this->sorter->updateSorting($attribute, $sorting);

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
            $result = $this->attributeRepository->find($id);
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
            $result = $this->optionRepository->find($id);
        } catch (EntityNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return $result;
    }
}
