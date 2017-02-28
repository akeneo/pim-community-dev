<?php

namespace PimEnterprise\Bundle\FilterBundle\Filter\Product;

use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter as OroChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectCompletenessRepositoryInterface;
use PimEnterprise\Component\TeamworkAssistant\Repository\ProjectRepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProjectCompletenessFilter extends OroChoiceFilter
{
    /** @var RequestParameters */
    protected $requestParams;

    /** @var ProjectRepositoryInterface */
    protected $projectRepository;

    /** @var ProjectCompletenessRepositoryInterface */
    protected $projectCompletenessRepo;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param FormFactoryInterface                   $factory
     * @param FilterUtility                          $util
     * @param RequestParameters                      $requestParams
     * @param ProjectRepositoryInterface             $projectRepository
     * @param ProjectCompletenessRepositoryInterface $projectCompletenessRepo
     * @param TokenStorageInterface                  $tokenStorage
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        RequestParameters $requestParams,
        ProjectRepositoryInterface $projectRepository,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepo,
        TokenStorageInterface $tokenStorage
    ) {
        $this->formFactory = $factory;
        $this->util = $util;
        $this->requestParams = $requestParams;
        $this->projectCompletenessRepo = $projectCompletenessRepo;
        $this->projectRepository = $projectRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Filter by permissions on category ids (category with owner permissions or not classified at all)
     *
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $ds, $data)
    {
        if (0 === $data['value']) {
            return false;
        }

        // RECUP ID VIEW
        $parameters = $this->requestParams->getRootParameterValue();
        $viewId = null;
        if (isset($parameters['_parameters']['view']['id']) && !empty($parameters['_parameters']['view']['id'])) {
            $viewId = $parameters['_parameters']['view']['id'];
        }

        if (null === $viewId) {
            return false;
        }

        // RECUP PROJET
        $project = $this->projectRepository->findOneBy(['datagridView' => $parameters['_parameters']['view']['id']]);

        if (null === $project) {
            return false;
        }

        // APPEL DU REPO
        $username = $this->tokenStorage->getToken()->getUsername();
        $productIds = $this->projectCompletenessRepo->findProductIds($project, $data['value'], $username);
        $productIds = empty($productIds) ? ['-1'] : $productIds;

        $this->util->applyFilter($ds, 'id', 'IN', $productIds);

        return true;
    }

    /**
     * @param array $data
     *
     * @return array|false
     */
    protected function parseData($data)
    {
        return $data;
    }
}
