<?php

namespace Akeneo\Pim\Enrichment\Bundle\Controller\InternalApi;

use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\PimDataGridBundle\Adapter\OroToPimGridFilterAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Controller to list the filters in the product grid.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductGridFilterController
{
    /** @var Manager */
    protected $datagridManager;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var SearchableRepositoryInterface */
    protected $attributeSearchRepository;

    /** @var NormalizerInterface */
    private $lightAttributeNormalizer;

    /** @var UserContext */
    private $userContext;

    /**
     * @param Manager                       $datagridManager
     * @param TokenStorageInterface         $tokenStorage
     * @param SearchableRepositoryInterface $attributeSearchRepository
     * @param NormalizerInterface           $lightAttributeNormalizer
     * @param UserContext                   $userContext
     */
    public function __construct(
        Manager $datagridManager,
        TokenStorageInterface $tokenStorage,
        SearchableRepositoryInterface $attributeSearchRepository,
        NormalizerInterface $lightAttributeNormalizer,
        UserContext $userContext
    ) {
        $this->datagridManager = $datagridManager;
        $this->tokenStorage = $tokenStorage;
        $this->attributeSearchRepository = $attributeSearchRepository;
        $this->lightAttributeNormalizer = $lightAttributeNormalizer;
        $this->userContext = $userContext;
    }

    /**
     * This will list the available product grid filters.
     *
     * A product grid filter can be:
     * - a 'system' filter (family, group, created_at, etc)
     * - an attribute useable_as_grid_filter
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $options = $request->get(
            'options',
            ['limit' => SearchableRepositoryInterface::FETCH_LIMIT, 'locale' => null, 'page' => 1]
        );

        $options['locale'] = $options['catalogLocale'] ?? null;
        unset($options['catalogLocale']);

        if ($request->get('identifiers', null) !== null) {
            $options['identifiers'] = array_unique(explode(',', $request->get('identifiers')));
        }

        $options['useable_as_grid_filter'] = true;
        $options['user_groups_ids'] = $this->retrieveUser()->getGroupsIds();

        $systemFilters = $this->getSystemFilters(
            $this->retrieveUser()->getUiLocale(),
            $request->get('search'),
            $options['limit'],
            $options['page']
        );
        $options['limit'] -= count($systemFilters);

        $attributes = $this->attributeSearchRepository->findBySearch(
            $request->get('search'),
            $options
        );

        $normalizedAttributes = array_map(function ($attribute) {
            return $this->lightAttributeNormalizer->normalize(
                $attribute,
                'internal_api',
                ['locale' => $this->userContext->getUiLocaleCode()]
            );
        }, $attributes);

        return new JsonResponse(array_merge($systemFilters, $normalizedAttributes));
    }

    /**
     * Return the filter configured in the grid ($datagridName)
     *
     * @param string      $locale
     * @param string|null $search
     * @param int         $limit
     * @param int         $page
     *
     * @return array
     */
    private function getSystemFilters(
        string $locale,
        ?string $search = '',
        int $limit = SearchableRepositoryInterface::FETCH_LIMIT,
        int $page = 1
    ) {
        if (null === $search) {
            $search = '';
        }
        $systemFilters = $this
            ->datagridManager->getConfigurationForGrid(OroToPimGridFilterAdapter::PRODUCT_GRID_NAME)
            ->offsetGetByPath('[filters][columns]');

        $formattedSystemFilters = [];
        foreach ($systemFilters as $code => $systemFilter) {
            $label = $systemFilter['label'];
            if (!in_array($code, ['scope', 'locale']) && (
                    '' === $search || strpos($code, $search) !== false || strpos($label, $search) !== false
                )) {
                $formattedSystemFilters[] = [
                    'code' => $code,
                    'labels' => [$locale => $label],
                    'group' => 'system'
                ];
            }
        }

        return array_slice($formattedSystemFilters, ($page - 1) * $limit, $limit);
    }

    private function retrieveUser(): UserInterface
    {
        return $this->tokenStorage->getToken()->getUser();
    }
}
