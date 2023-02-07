import React, {FC, useEffect, useRef, useState} from 'react';
import {Search, useAutoFocus, Table, Badge} from 'akeneo-design-system';
import {useDebounceCallback, useTranslate, useFeatureFlags, useUserContext} from '@akeneo-pim-community/shared';
import {useAttributeGroupPermissions, useAttributeGroupsIndexState, useFilteredAttributeGroups} from '../../../hooks';
import {AttributeGroup} from '../../../models';
import {NoResults} from '../../shared';
import {getLabel} from 'pimui/js/i18n';

type Props = {
  groups: AttributeGroup[];
  onGroupCountChange: (newGroupCount: number) => void;
};

const AttributeGroupsDataGrid: FC<Props> = ({groups, onGroupCountChange}) => {
  const {refreshOrder} = useAttributeGroupsIndexState();
  const {sortGranted} = useAttributeGroupPermissions();
  const userContext = useUserContext();
  const {filteredGroups, search} = useFilteredAttributeGroups(groups);
  const translate = useTranslate();
  const [searchString, setSearchString] = useState('');
  const inputRef = useRef<HTMLInputElement>(null);
  const featureFlags = useFeatureFlags();

  useAutoFocus(inputRef);

  const debouncedSearch = useDebounceCallback(search, 300);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  const onReorder = async (order: number[]) => {
    await refreshOrder(order.map(index => groups[index]));
  };

  useEffect(() => {
    onGroupCountChange(filteredGroups.length);
  }, [filteredGroups.length]);

  return (
    <>
      <Search
        sticky={0}
        placeholder={translate('pim_common.search')}
        searchValue={searchString}
        onSearchChange={onSearch}
        inputRef={inputRef}
      >
        <Search.ResultCount>
          {translate('pim_common.result_count', {itemsCount: filteredGroups.length}, filteredGroups.length)}
        </Search.ResultCount>
      </Search>
      {searchString !== '' && filteredGroups.length === 0 ? (
        <NoResults
          title={translate('pim_enrich.entity.attribute_group.grid.no_search_result')}
          subtitle={translate('pim_datagrid.no_results_subtitle')}
        />
      ) : (
        <Table isDragAndDroppable={sortGranted} isSelectable={true} onReorder={order => onReorder(order)}>
          <Table.Header>
            <Table.HeaderCell>{translate('pim_enrich.entity.attribute_group.grid.columns.name')}</Table.HeaderCell>
            {featureFlags.isEnabled('data_quality_insights') && (
              <Table.HeaderCell>
                {translate('akeneo_data_quality_insights.attribute_group.dqi_status')}
              </Table.HeaderCell>
            )}
          </Table.Header>
          <Table.Body>
            {filteredGroups.map(group => (
              <Table.Row key={group.code} isSelected={false} onSelectToggle={() => {}}>
                <Table.Cell>{getLabel(group.labels, userContext.get('catalogLocale'), group.code)}</Table.Cell>
                {featureFlags.isEnabled('data_quality_insights') && (
                  <Table.Cell>
                    <Badge level={group.is_dqi_activated ? 'primary' : 'danger'}>
                      {translate(
                        `akeneo_data_quality_insights.attribute_group.${
                          group.is_dqi_activated ? 'activated' : 'disabled'
                        }`
                      )}
                    </Badge>
                  </Table.Cell>
                )}
              </Table.Row>
            ))}
          </Table.Body>
        </Table>
      )}
    </>
  );
};

export {AttributeGroupsDataGrid};
