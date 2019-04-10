<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Filter;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectProductIdsByUserAndDraftStatusQueryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectProductModelIdsByUserAndDraftStatusQueryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\FilterBundle\Filter\FilterInterface;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class DraftStatusFilter implements FilterInterface
{
    private $choiceFilter;

    private $filterUtility;

    private $selectProductIdsByUserAndDraftStatusQuery;

    private $selectProductModelIdsByUserAndDraftStatusQuery;

    private $userContext;

    public function __construct(
        ChoiceFilter $choiceFilter,
        ProductFilterUtility $filterUtility,
        SelectProductIdsByUserAndDraftStatusQueryInterface $selectProductIdsByUserAndDraftStatusQuery,
        SelectProductModelIdsByUserAndDraftStatusQueryInterface $selectProductModelIdsByUserAndDraftStatusQuery,
        UserContext $userContext
    ) {
        $this->choiceFilter = $choiceFilter;
        $this->filterUtility = $filterUtility;
        $this->selectProductIdsByUserAndDraftStatusQuery = $selectProductIdsByUserAndDraftStatusQuery;
        $this->selectProductModelIdsByUserAndDraftStatusQuery = $selectProductModelIdsByUserAndDraftStatusQuery;
        $this->userContext = $userContext;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(FilterDatasourceAdapterInterface $filterDatasource, $data)
    {
        $filterValue = isset($data['value']) ? $data['value'] : null;

        if (null === $filterValue) {
            return false;
        }

        $user = $this->userContext->getUser();
        if (!$user instanceof UserInterface) {
            throw new \Exception('Draft filter is only useable when user is authenticated');
        }

        // 0:
        // 1:
        // 2: Waiting for approval
        // Calculate draft statuses
        $draftStatuses = [0];

        $productIds = $this->selectProductIdsByUserAndDraftStatusQuery->execute($user->getUsername(), $draftStatuses);
        $productModelIds = $this->selectProductModelIdsByUserAndDraftStatusQuery->execute($user->getUsername(), $draftStatuses);

        $this->filterUtility->applyFilter($filterDatasource, 'product_id', 'IN', $productIds);
        $this->filterUtility->applyFilter($filterDatasource, 'product_model_id', 'IN', $productModelIds);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function init($name, array $params): void
    {
        $this->choiceFilter->init($name, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->choiceFilter->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->choiceFilter->getForm();
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return $this->choiceFilter->getMetadata();
    }
}
