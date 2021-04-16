import React from 'react';
import {Badge, IconButton, DeleteIcon, Table} from 'akeneo-design-system';
import {useTranslate, useRouter} from '@akeneo-pim-community/legacy-bridge';
import {AssociationType, NoResults} from '@akeneo-pim-community/settings-ui';
import styled from 'styled-components';

type Props = {
  associationTypes: AssociationType[];
  sortDirection: string;
  onDirectionChange: (direction: string) => void;
  deleteAssociationType: (associationType: AssociationType) => void;
};

const getTableCellSortDirection = (sortDirection: string) => {
  switch (sortDirection) {
    case 'ASC':
      return 'ascending';
    case 'DESC':
      return 'descending';
    default:
      return 'none';
  }
};

const TableHeaderCellLabel = styled(Table.HeaderCell)`
  width: 35%;
`;

const TableActionCell = styled(Table.ActionCell)`
  width: 50px;
`;

const AssociationTypesDataGrid = ({
  associationTypes,
  sortDirection,
  onDirectionChange,
  deleteAssociationType,
}: Props) => {
  const translate = useTranslate();
  const router = useRouter();

  const renderBooleanCellValue = (cellValue: boolean) => (
    <Badge level={cellValue ? 'primary' : 'danger'}>
      {cellValue ? translate('pim_common.yes') : translate('pim_common.no')}
    </Badge>
  );
  if (associationTypes.length === 0) {
    return (
      <NoResults
        title={translate('pim_datagrid.no_results', {
          entityHint: translate('pim_enrich.entity.association_type.label'),
        })}
        subtitle={translate('pim_datagrid.no_results_subtitle')}
      />
    );
  }

  return (
    // Class name "grid" to keep compatibility with the legacy end-to-end tests
    <Table className={'grid'}>
      <Table.Header>
        <TableHeaderCellLabel
          isSortable
          sortDirection={getTableCellSortDirection(sortDirection)}
          onDirectionChange={onDirectionChange}
        >
          {translate('pim_common.label')}
        </TableHeaderCellLabel>
        <Table.HeaderCell>{translate('pim_enrich.entity.association_type.property.is_quantified')}</Table.HeaderCell>
        <Table.HeaderCell>{translate('pim_enrich.entity.association_type.property.is_two_way')}</Table.HeaderCell>
        <Table.HeaderCell />
      </Table.Header>
      <Table.Body>
        {associationTypes.map(associationType => {
          return (
            <Table.Row
              key={associationType.id}
              onClick={() => {
                router.redirect(associationType.editLink);
              }}
            >
              <Table.Cell rowTitle>{associationType.label}</Table.Cell>
              <Table.Cell>{renderBooleanCellValue(associationType.isQuantified)}</Table.Cell>
              <Table.Cell>{renderBooleanCellValue(associationType.isTwoWay)}</Table.Cell>
              <TableActionCell>
                <IconButton
                  icon={<DeleteIcon />}
                  onClick={() => deleteAssociationType(associationType)}
                  title={translate('pim_common.delete')}
                  ghost="borderless"
                  level="tertiary"
                />
              </TableActionCell>
            </Table.Row>
          );
        })}
      </Table.Body>
    </Table>
  );
};

export {AssociationTypesDataGrid};
