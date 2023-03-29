import React from 'react';
import styled from 'styled-components';
import {Table} from 'akeneo-design-system';
import {useTranslate, useFeatureFlags, useSecurity} from '@akeneo-pim-community/shared';
import {AttributeGroup} from '../../../models';
import {NoResults} from '../../shared';
import {AttributeGroupRow} from './AttributeGroupRow';

const TableWrapper = styled.div<{isSelectable: boolean}>`
  ${({isSelectable}) => (isSelectable ? 'margin-left: -40px;' : '')}
  padding: 0 40px;
`;

type AttributeGroupListProps = {
  filteredAttributeGroups: AttributeGroup[];
  attributeGroups: AttributeGroup[];
  isItemSelected: (attributeGroup: AttributeGroup) => boolean;
  onSelectionChange: (attributeGroup: AttributeGroup, selected: boolean) => void;
  onReorder: (newIndices: number[]) => void;
};

const AttributeGroupList = ({
  filteredAttributeGroups,
  attributeGroups,
  isItemSelected,
  onSelectionChange,
  onReorder,
}: AttributeGroupListProps) => {
  const translate = useTranslate();
  const {isEnabled} = useFeatureFlags();
  const {isGranted} = useSecurity();

  const shouldDisplayPlaceholder = 0 === filteredAttributeGroups.length && attributeGroups.length !== 0;
  const shouldDisplayDQICell = isEnabled('data_quality_insights');
  const allAttributeGroupsAreDisplayed = filteredAttributeGroups.length === attributeGroups.length;
  const canDragAndDrop = isGranted('pim_enrich_attributegroup_sort') && allAttributeGroupsAreDisplayed;
  const canSelect = isGranted('pim_enrich_attributegroup_mass_delete');

  return (
    <>
      {shouldDisplayPlaceholder ? (
        <NoResults
          title={translate('pim_common.no_search_result')}
          subtitle={translate('pim_datagrid.no_results_subtitle')}
        />
      ) : (
        <TableWrapper isSelectable={canSelect}>
          <Table isDragAndDroppable={canDragAndDrop} isSelectable={canSelect} onReorder={onReorder}>
            <Table.Header>
              <Table.HeaderCell>{translate('pim_common.label')}</Table.HeaderCell>
              <Table.HeaderCell>
                {translate('pim_enrich.entity.attribute_group.grid.columns.attribute_count')}
              </Table.HeaderCell>
              {shouldDisplayDQICell && (
                <Table.HeaderCell>
                  {translate('akeneo_data_quality_insights.attribute_group.dqi_status')}
                </Table.HeaderCell>
              )}
            </Table.Header>
            <Table.Body>
              {filteredAttributeGroups.map(attributeGroup => (
                <AttributeGroupRow
                  key={attributeGroup.code}
                  attributeGroup={attributeGroup}
                  isSelected={isItemSelected(attributeGroup)}
                  onSelectionChange={onSelectionChange}
                />
              ))}
            </Table.Body>
          </Table>
        </TableWrapper>
      )}
    </>
  );
};

export {AttributeGroupList};
