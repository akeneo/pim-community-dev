<?php

namespace Oro\Bundle\PimDataGridBundle\Controller\Rest;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\PimDataGridBundle\Manager\DatagridViewManager;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * REST Controller for Datagrid Views.
 * Handle basic CRUD actions.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DatagridViewController
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var DatagridViewRepositoryInterface */
    protected $datagridViewRepo;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var DatagridViewManager */
    protected $datagridViewManager;

    /** @var SaverInterface */
    protected $saver;

    /** @var RemoverInterface */
    protected $remover;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var CollectionFilterInterface */
    protected $datagridViewFilter;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var SimpleFactoryInterface */
    protected $factory;

    public function __construct(
        NormalizerInterface $normalizer,
        DatagridViewRepositoryInterface $datagridViewRepo,
        TokenStorageInterface $tokenStorage,
        DatagridViewManager $datagridViewManager,
        SaverInterface $saver,
        RemoverInterface $remover,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        CollectionFilterInterface $datagridViewFilter,
        ObjectUpdaterInterface $updater,
        SimpleFactoryInterface $factory
    ) {
        $this->normalizer = $normalizer;
        $this->datagridViewRepo = $datagridViewRepo;
        $this->tokenStorage = $tokenStorage;
        $this->datagridViewManager = $datagridViewManager;
        $this->saver = $saver;
        $this->remover = $remover;
        $this->validator = $validator;
        $this->translator = $translator;
        $this->datagridViewFilter = $datagridViewFilter;
        $this->updater = $updater;
        $this->factory = $factory;
    }

    /**
     * Return the list of all Datagrid Views that belong to the current user for the given $alias grid.
     * Response data is in Json format and is paginated.
     *
     * @param Request $request
     * @param string  $alias
     *
     * @return JsonResponse
     */
    public function indexAction(Request $request, $alias)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $options = $request->query->get('options', ['limit' => 20, 'page' => 1]);
        $term = $request->query->get('search', '');

        $views = $this->datagridViewRepo->findDatagridViewBySearch($user, $alias, $term, $options);
        $views = $this->datagridViewFilter->filterCollection($views, 'pim.internal_api.datagrid_view.view');

        $normalizedViews = $this->normalizer->normalize($views, 'internal_api');

        return new JsonResponse($normalizedViews);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function typesAction(Request $request): JsonResponse
    {
        $result = [];
        $user = $this->tokenStorage->getToken()->getUser();
        $types = $this->datagridViewRepo->getDatagridViewTypeByUser($user);
        foreach ($types as $type) {
            $result[] = $type['datagridAlias'];
        }

        return new JsonResponse($result);
    }

    /**
     * Return the Datagrid View that belongs to the current user, with the given view $identifier.
     * Response data is in Json format, 404 is sent if there is no result.
     *
     * @param string $identifier
     *
     * @return JsonResponse|NotFoundHttpException
     */
    public function getAction($identifier)
    {
        $view = $this->datagridViewRepo->find($identifier);
        if (null === $view) {
            return new JsonResponse(null, 404);
        }

        $view = current($this->datagridViewFilter->filterCollection([$view], 'pim.internal_api.datagrid_view.view'));
        if (null === $view) {
            return new JsonResponse(null, 404);
        }

        $normalizedView = $this->normalizer->normalize($view, 'internal_api');

        return new JsonResponse($normalizedView);
    }

    /**
     * Save the Datagrid View received through the $request for the grid with the given $alias.
     *
     * If any errors occur during the writing process, a Json response is sent with {'errors' => 'Error message'}.
     * If success, return a Json response with the id of the saved View.
     *
     * @param Request $request
     * @param string  $alias
     *
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function saveAction(Request $request, $alias)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $view = $request->request->get('view', null);

        if (null === $view) {
            throw new BadRequestHttpException('Parameter "view" needed in the request.');
        }

        if (isset($view['id'])) {
            $creation = false;
            $datagridView = $this->datagridViewRepo->find($view['id']);
        } else {
            $creation = true;
            $datagridView = $this->factory->create();

            $view['owner'] = $this->tokenStorage->getToken()->getUser()->getUsername();
            $view['datagrid_alias'] = $alias;
        }

        if (null === $datagridView) {
            throw new NotFoundHttpException();
        }

        $this->updater->update($datagridView, $view);

        $violations = $this->validator->validate($datagridView);

        if ($violations->count()) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $this->translator->trans($violation->getMessage());
            }

            return new JsonResponse($messages, 400);
        }

        $this->saver->save($datagridView);

        if ($creation) {
            $request->getSession()->getFlashBag()
                ->add('success', $this->translator->trans('pim_datagrid.view_selector.flash.created'));
        }

        return new JsonResponse(['id' => $datagridView->getId()]);
    }

    /**
     * Remove the Datagrid View with the given $identifier.
     *
     * If any errors occur during the process, a Json response is sent with {'errors' => 'Error message'}.
     * If success, return an empty Json response with code 204 (No content).
     *
     * @param Request $request
     * @param string  $identifier
     *
     * @return Response
     */
    public function removeAction(Request $request, $identifier)
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $view = $this->datagridViewRepo->findOneBy(['owner' => $user, 'id' => $identifier]);

        if (null === $view) {
            return new JsonResponse($this->translator->trans('pim_datagrid.view_selector.flash.not_removable'), 404);
        }

        $this->remover->remove($view);
        $request->getSession()->getFlashBag()
            ->add('success', $this->translator->trans('pim_datagrid.view_selector.flash.removed'));

        return new JsonResponse(null, 204);
    }

    /**
     * Return the default columns for the grid with the given $alias.
     * Response data is in Json format.
     *
     * Eg.: ['sku', 'name', 'brand']
     *
     * @param string $alias
     *
     * @return JsonResponse
     */
    public function defaultViewColumnsAction($alias)
    {
        $columns = $this->datagridViewManager->getDefaultColumns($alias);

        return new JsonResponse($columns);
    }

    /**
     * Return the current user default Datagrid View object for the grid with the given $alias.
     * Response data is in Json format.
     *
     * @param string $alias
     *
     * @return JsonResponse
     */
    public function getUserDefaultDatagridViewAction($alias)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $view = $user->getDefaultGridView($alias);

        if (null !== $view) {
            $view = $this->normalizer->normalize($view, 'internal_api');
        }

        return new JsonResponse(['view' => $view]);
    }

    /**
     * List available datagrid columns
     *
     * @param string $alias
     *
     * @return JsonResponse
     */
    public function listColumnsAction($alias)
    {
        return new JsonResponse($this->datagridViewManager->getColumnChoices($alias));
    }
}
