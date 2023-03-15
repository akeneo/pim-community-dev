import React, {FC, useRef, useState} from 'react';
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
import {PageHeader, useRoute, useTranslate, useUserContext, PimView} from '@akeneo-pim-community/shared';
import {AttributeGroupsCreateButton, AttributeGroupList, MassDeleteAttributeGroupsModal} from '../components';
import {AttributeGroup, getImpactedAndTargetAttributeGroups} from '../models';
import {useAttributeGroups} from '../hooks/attribute-groups/useAttributeGroups';

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
  const inputRef = useRef<HTMLInputElement>(null);
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();
  const [attributeGroups, reorderAttributeGroups, isPending] = useAttributeGroups();
  const [searchValue, setSearchValue] = useState('');
  const catalogLocale = useUserContext().get('catalogLocale');
  const [selection, selectionState, isItemSelected, onSelectionChange, onSelectAllChange, selectedCount] =
    useSelection<AttributeGroup>(attributeGroups.length);
  const translate = useTranslate();
  const settingsHomePageRoute = `#${useRoute('pim_settings_index')}`;

  const filteredAttributeGroups = attributeGroups.filter((attributeGroup: AttributeGroup) => {
    return (attributeGroup.labels[catalogLocale] ?? attributeGroup.code)
      .toLowerCase()
      .includes(searchValue.toLowerCase().trim());
  });

  const [impactedAttributeGroups, availableTargetAttributeGroups] = getImpactedAndTargetAttributeGroups(
    attributeGroups,
    selection
  );

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
            {count: filteredAttributeGroups.length},
            filteredAttributeGroups.length
          )}
        </PageHeader.Title>
      </PageHeader>
      <Content>
        <Search
          sticky={0}
          placeholder={translate('pim_common.search')}
          searchValue={searchValue}
          onSearchChange={setSearchValue}
          inputRef={inputRef}
        >
          <Search.ResultCount>
            {translate('pim_common.result_count', {itemsCount: filteredAttributeGroups.length}, filteredAttributeGroups.length)}
          </Search.ResultCount>
        </Search>
        <AttributeGroupList
          filteredAttributeGroups={filteredAttributeGroups}
          attributeGroups={attributeGroups}
          isItemSelected={isItemSelected}
          onSelectionChange={onSelectionChange}
          onReorder={reorderAttributeGroups}
        />
      </Content>
      {!isPending && (
        <Toolbar isVisible={!!selectionState}>
          <Toolbar.SelectionContainer>
            <Checkbox checked={selectionState} onChange={value => onSelectAllChange(value)} />
            <Dropdown>
              <ArrowDownIcon title={translate('pim_enrich.entity.attribute_group.dropdown.label')} onClick={openDropdown} />
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
