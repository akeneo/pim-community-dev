<?php

namespace Oro\Bundle\PimDataGridBundle\Controller\Rest;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\PimDataGridBundle\Manager\DatagridViewManager;
use Oro\Bundle\PimDataGridBundle\Repository\DatagridViewRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

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
    protected NormalizerInterface $normalizer;
    protected DatagridViewRepositoryInterface $datagridViewRepo;
    protected TokenStorageInterface $tokenStorage;
    protected DatagridViewManager $datagridViewManager;
    protected SaverInterface $saver;
    protected RemoverInterface $remover;
    protected ValidatorInterface $validator;
    protected TranslatorInterface $translator;
    protected CollectionFilterInterface $datagridViewFilter;
    protected ObjectUpdaterInterface $updater;
    protected SimpleFactoryInterface $factory;

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
     */
    public function indexAction(Request $request, string $alias): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $user = $this->tokenStorage->getToken()->getUser();

        $options = $request->query->get('options', []);
        $options = array_merge(['limit' => 20, 'page' => 1], $options);
        $term = $request->query->get('search', '');

        $views = $this->datagridViewRepo->findDatagridViewBySearch($user, $alias, $term, $options);
        $moreResults = (count($views) === (int)$options['limit']);
        $views = $this->datagridViewFilter->filterCollection($views, 'pim.internal_api.datagrid_view.view');

        $normalizedViews = $this->normalizer->normalize($views, 'internal_api');

        return new JsonResponse([
            'results' => $normalizedViews,
            'more' => $moreResults,
        ]);
    }

    public function typesAction(): JsonResponse
    {
        $user = $this->tokenStorage->getToken()->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        return new JsonResponse($this->datagridViewRepo->getDatagridViewAliasesByUser($user));
    }

    /**
     * Return the Datagrid View that belongs to the current user, with the given view $identifier.
     * Response data is in Json format, 404 is sent if there is no result.
     */
    public function getAction(string $identifier): JsonResponse
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
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function saveAction(Request $request, string $alias): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $view = $request->request->get('view', null);

        if (null === $view) {
            throw new BadRequestHttpException('Parameter "view" needed in the request.');
        }

        $loggedUsername = $this->tokenStorage->getToken()->getUser()->getUsername();
        if (isset($view['id'])) {
            $creation = false;
            $datagridView = $this->datagridViewRepo->findOneBy(['id' => $view['id'], 'datagridAlias' => $alias]);
            if (null === $datagridView) {
                throw new NotFoundHttpException();
            }

            $owner = $datagridView->getOwner();
            if (!$owner instanceof UserInterface || $owner->getUsername() !== $loggedUsername) {
                throw new AccessDeniedException();
            }

            // Once the view is created we cannot change its type.
            unset($view['type']);
        } else {
            $creation = true;
            $datagridView = $this->factory->create();

            $view['owner'] = $loggedUsername;
            $view['datagrid_alias'] = $alias;
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
     */
    public function removeAction(Request $request, string $identifier): Response
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
     */
    public function defaultViewColumnsAction(string $alias): JsonResponse
    {
        $columns = $this->datagridViewManager->getDefaultColumns($alias);

        return new JsonResponse($columns);
    }

    /**
     * Return the current user default Datagrid View object for the grid with the given $alias.
     * Response data is in Json format.
     */
    public function getUserDefaultDatagridViewAction(string $alias): JsonResponse
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
     */
    public function listColumnsAction(string $alias): JsonResponse
    {
        return new JsonResponse($this->datagridViewManager->getColumnChoices($alias));
    }
}
