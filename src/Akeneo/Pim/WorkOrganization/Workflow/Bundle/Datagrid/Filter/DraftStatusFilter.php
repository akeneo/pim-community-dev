<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Filter;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectProductModelIdsByUserAndDraftStatusQueryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectProductUuidsByUserAndDraftStatusQueryInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\FilterBundle\Datasource\FilterDatasourceAdapterInterface;
use Oro\Bundle\FilterBundle\Filter\ChoiceFilter;
use Oro\Bundle\PimFilterBundle\Filter\ProductFilterUtility;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class DraftStatusFilter extends ChoiceFilter
{
    const WORKING_COPY = 0;
    const IN_PROGRESS = 1;
    const WAITING_FOR_APPROVAL = 2;

    public function __construct(
        FormFactoryInterface $formFactory,
        ProductFilterUtility $filterUtility,
        private SelectProductUuidsByUserAndDraftStatusQueryInterface $selectProductUuidsByUserAndDraftStatusQuery,
        private SelectProductModelIdsByUserAndDraftStatusQueryInterface $selectProductModelIdsByUserAndDraftStatusQuery,
        private UserContext $userContext
    ) {
        parent::__construct($formFactory, $filterUtility);
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
            case self::WAITING_FOR_APPROVAL:
                $draftStatuses = [EntityWithValuesDraftInterface::READY];
                $operator = 'IN';
                break;
            case self::IN_PROGRESS:
                $draftStatuses = [EntityWithValuesDraftInterface::IN_PROGRESS];
                $operator = 'IN';
                break;
            default:
                throw new \LogicException('Expected filter value should be between 0 and 2');
        }

        $user = $this->userContext->getUser();
        if (!$user instanceof UserInterface) {
            throw new \Exception('Draft filter is only useable when user is authenticated');
        }

        $productUuids = $this->selectProductUuidsByUserAndDraftStatusQuery->execute($user->getUserIdentifier(), $draftStatuses);
        $productModelIds = $this->selectProductModelIdsByUserAndDraftStatusQuery->execute($user->getUserIdentifier(), $draftStatuses);
        $esIds = $this->prepareIdsForEsFilter($productUuids, $productModelIds);
        $esIds = empty($esIds) ? ['null'] : $esIds;

        $this->util->applyFilter($filterDatasource, 'id', $operator, $esIds);

        return true;
    }

    private function prepareIdsForEsFilter(array $productUuids, array $productModelIds): array
    {
        $esValueIds = [];
        foreach ($productUuids as $productUuid) {
            $esValueIds[] = 'product_' . $productUuid->toString();
        }
        foreach ($productModelIds as $productModelId) {
            $esValueIds[] = 'product_model_' . $productModelId;
        }

        return $esValueIds;
    }
}
