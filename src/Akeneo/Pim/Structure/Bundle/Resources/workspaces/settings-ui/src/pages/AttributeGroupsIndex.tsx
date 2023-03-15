import React, {FC, useEffect, useRef, useState} from 'react';
import styled from 'styled-components';
import {
  Breadcrumb,
  Checkbox,
  Toolbar,
  useSelection,
  useBooleanState,
  Search,
  Dropdown,
  ArrowDownIcon,
  useAutoFocus,
} from 'akeneo-design-system';
import {PageHeader, useRoute, useTranslate, PimView} from '@akeneo-pim-community/shared';
import {AttributeGroupList, AttributeGroupsCreateButton, MassDeleteAttributeGroupsModal} from '../components';
import {useAttributeGroupsIndexState, useFilteredAttributeGroups} from '../hooks';
import {AttributeGroup, getImpactedAndTargetAttributeGroups} from '../models';

const Content = styled.div`
  flex: 1;
  overflow-y: auto;
  padding: 0 40px;
`;

const Page = styled.div`
  height: 100%;
  display: flex;
  flex-direction: column;
`;

const AttributeGroupsIndex: FC = () => {
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();
  const {attributeGroups, load, refreshOrder, isPending} = useAttributeGroupsIndexState();
  const [selection, selectionState, isItemSelected, onSelectionChange, onSelectAllChange, selectedCount] =
    useSelection<AttributeGroup>(attributeGroups.length);
  const translate = useTranslate();
  const settingsHomePageRoute = `#${useRoute('pim_settings_index')}`;
  const {filteredGroups, search} = useFilteredAttributeGroups(attributeGroups);
  const [searchValue, setSearchValue] = useState<string>('');
  const inputRef = useRef<HTMLInputElement>(null);
  const [impactedAttributeGroups, availableTargetAttributeGroups] = getImpactedAndTargetAttributeGroups(
    attributeGroups,
    selection
  );

  const onSearch = (searchValue: string) => {
    setSearchValue(searchValue);
    search(searchValue);
  };

  const handleReorder = (order: number[]) => refreshOrder(order.map(index => attributeGroups[index]));
  useEffect(() => {
    (async () => {
      await load();
    })();
  }, [load]);

  useAutoFocus(inputRef);

  return (
    <Page>
      <PageHeader showPlaceholder={isPending}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={settingsHomePageRoute}>{translate('pim_menu.tab.settings')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_enrich.entity.attribute_group.plural_label')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            viewName="pim-menu-user-navigation"
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
          />
        </PageHeader.UserActions>
        <PageHeader.Actions>
          <AttributeGroupsCreateButton attributeGroupCount={attributeGroups.length} />
        </PageHeader.Actions>
        <PageHeader.Title>
          {translate(
            'pim_enrich.entity.attribute_group.result_count',
            {count: filteredGroups.length},
            filteredGroups.length
          )}
        </PageHeader.Title>
      </PageHeader>
      <Content>
        <Search
          sticky={0}
          placeholder={translate('pim_common.search')}
          searchValue={searchValue}
          onSearchChange={onSearch}
          inputRef={inputRef}
        >
          <Search.ResultCount>
            {translate('pim_common.result_count', {itemsCount: filteredGroups.length}, filteredGroups.length)}
          </Search.ResultCount>
        </Search>
        <AttributeGroupList
          filteredAttributeGroups={filteredGroups}
          attributeGroups={attributeGroups}
          isItemSelected={isItemSelected}
          onSelectionChange={onSelectionChange}
          selectedCount={selectedCount}
          onSelectAllChange={onSelectAllChange}
          onReorder={handleReorder}
        />
      </Content>
      {!isPending && (
        <Toolbar isVisible={!!selectionState}>
          <Toolbar.SelectionContainer>
            <Checkbox checked={selectionState} onChange={value => onSelectAllChange(value)} />
            <Dropdown>
              <ArrowDownIcon onClick={openDropdown} />
              {isDropdownOpen && (
                <Dropdown.Overlay onClose={closeDropdown}>
                  <Dropdown.Header>
                    <Dropdown.Title>{translate('pim_enrich.entity.attribute_group.dropdown.label')}</Dropdown.Title>
                  </Dropdown.Header>
                  <Dropdown.ItemCollection>
                    <Dropdown.Item
                      onClick={() => {
                        onSelectAllChange(true);
                        closeDropdown();
                      }}
                    >
                      {translate('pim_enrich.entity.attribute_group.dropdown.all')}
                    </Dropdown.Item>
                    <Dropdown.Item
                      onClick={() => {
                        onSelectAllChange(false);
                        closeDropdown();
                      }}
                    >
                      {translate('pim_enrich.entity.attribute_group.dropdown.none')}
                    </Dropdown.Item>
                  </Dropdown.ItemCollection>
                </Dropdown.Overlay>
              )}
            </Dropdown>
          </Toolbar.SelectionContainer>
          <Toolbar.LabelContainer>
            {translate('pim_enrich.entity.attribute_group.selected', {count: selectedCount}, selectedCount)}
          </Toolbar.LabelContainer>
          <Toolbar.ActionsContainer>
            {0 < selectedCount && (
              <MassDeleteAttributeGroupsModal
                impactedAttributeGroups={impactedAttributeGroups}
                availableTargetAttributeGroups={availableTargetAttributeGroups}
              />
            )}
          </Toolbar.ActionsContainer>
        </Toolbar>
      )}
    </Page>
  );
};

export {AttributeGroupsIndex};
