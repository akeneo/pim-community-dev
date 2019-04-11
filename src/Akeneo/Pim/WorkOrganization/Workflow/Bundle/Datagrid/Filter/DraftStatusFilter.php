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

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Builder\EntityWithValuesDraftBuilder;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
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
    private const WORKING_COPY = 0;
    private const IN_PROGRESS = 1;
    private const WAITING_FOR_APPROVAL = 2;

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

        switch ($filterValue) {
            case self::WORKING_COPY:
                $draftStatuses = [EntityWithValuesDraftInterface::IN_PROGRESS, EntityWithValuesDraftInterface::READY];
                $operator = 'NOT IN';

                break;
            case self::IN_PROGRESS:
                $draftStatuses = [EntityWithValuesDraftInterface::IN_PROGRESS];
                $operator = 'IN';

                break;
            case self::WAITING_FOR_APPROVAL:
                $draftStatuses = [EntityWithValuesDraftInterface::READY];
                $operator = 'IN';
                break;
            default:
                throw new \LogicException('Expected filter value should be between 0 and 2');
        }

        $user = $this->userContext->getUser();
        if (!$user instanceof UserInterface) {
            throw new \Exception('Draft filter is only useable when user is authenticated');
        }

        $productIds = $this->selectProductIdsByUserAndDraftStatusQuery->execute($user->getUsername(), $draftStatuses);
        $productModelIds = $this->selectProductModelIdsByUserAndDraftStatusQuery->execute($user->getUsername(), $draftStatuses);
        $esIds = $this->prepareIdsForEsFilter($productIds, $productModelIds);

        $this->filterUtility->applyFilter($filterDatasource, 'id', $operator, $esIds);


        return true;
    }

    private function prepareIdsForEsFilter(array $productIds, array $productModelIds): array
    {
        $esValueIds = [];
        foreach ($productIds as $productId) {
            $esValueIds[] = 'product_'. $productId;
        }
        foreach ($productModelIds as $productModelId) {
            $esValueIds[] = 'product_model_'. $productModelId;
        }

        return $esValueIds;
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
