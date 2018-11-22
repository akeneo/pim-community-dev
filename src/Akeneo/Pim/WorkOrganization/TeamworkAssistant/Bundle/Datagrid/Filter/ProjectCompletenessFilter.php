<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (https://www.akeneo.com/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Datagrid\Filter;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectCompletenessRepositoryInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Repository\ProjectRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter as OroChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterUtility;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Allow to filter products regarding their completeness in a given Teamwork Assistant project.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ProjectCompletenessFilter extends OroChoiceFilter
{
    const OWNER_TODO = 1;
    const OWNER_IN_PROGRESS = 2;
    const OWNER_DONE = 3;
    const CONTRIBUTOR_TODO = 4;
    const CONTRIBUTOR_IN_PROGRESS = 5;
    const CONTRIBUTOR_DONE = 6;

    /** @var RequestParameters */
    protected $requestParams;

    /** @var ProjectRepositoryInterface */
    protected $projectRepository;

    /** @var ProjectCompletenessRepositoryInterface */
    protected $projectCompletenessRepo;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param FormFactoryInterface                   $factory
     * @param FilterUtility                          $util
     * @param RequestParameters                      $requestParams
     * @param ProjectRepositoryInterface             $projectRepository
     * @param ProjectCompletenessRepositoryInterface $projectCompletenessRepo
     * @param TokenStorageInterface                  $tokenStorage
     * @param AttributeRepositoryInterface           $attributeRepository
     */
    public function __construct(
        FormFactoryInterface $factory,
        FilterUtility $util,
        RequestParameters $requestParams,
        ProjectRepositoryInterface $projectRepository,
        ProjectCompletenessRepositoryInterface $projectCompletenessRepo,
        TokenStorageInterface $tokenStorage,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->formFactory = $factory;
        $this->util = $util;
        $this->requestParams = $requestParams;
        $this->projectCompletenessRepo = $projectCompletenessRepo;
        $this->projectRepository = $projectRepository;
        $this->tokenStorage = $tokenStorage;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $datasource, $data)
    {
        if (0 === $data['value']) {
            return false;
        }

        $parameters = $this->requestParams->getRootParameterValue();
        $viewId = null;
        if (isset($parameters['_parameters']['view']['id']) && '' !== $parameters['_parameters']['view']['id']) {
            $viewId = $parameters['_parameters']['view']['id'];
        }

        if (null === $viewId) {
            return false;
        }

        $project = $this->projectRepository->findOneBy(['datagridView' => $viewId]);
        if (null === $project) {
            return false;
        }

        $username = $this->tokenStorage->getToken()->getUsername();
        $productIdentifiers = $this->projectCompletenessRepo->findProductIdentifiers($project, $data['value'], $username);

        // If the user has access to zero product in the project, we have to return "no result". So we add an
        // "always-false" filter by looking for products with "identifier = null"
        $productIdentifiers = $productIdentifiers ?? [null];

        $this->util->applyFilter(
            $datasource,
            $this->attributeRepository->getIdentifierCode(),
            'IN',
            $productIdentifiers
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function parseData($data)
    {
        return $data;
    }
}
