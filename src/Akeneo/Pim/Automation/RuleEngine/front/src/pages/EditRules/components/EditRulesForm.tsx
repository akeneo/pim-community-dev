import React from 'react';
import styled from 'styled-components';
import {useTabState, Tab, TabList, TabPanel} from 'reakit/Tab';
import {Condition, LocaleCode} from '../../../models';
import {RulesBuilder} from './RulesBuilder';
import {RuleProperties} from './RuleProperties';
import {Locale} from '../../../models';
import {IndexedScopes} from '../../../repositories/ScopeRepository';
import {useFormContext} from 'react-hook-form';
import {useTranslate} from '../../../dependenciesTools/hooks';
import {Action} from '../../../models/Action';

const getTabBorder = ({id, selectedId, theme}: any): string | number => {
  if (id === selectedId) {
    return `3px solid ${theme.color.purple100}`;
  }
  return 0;
};

const getTabColor = ({id, selectedId, theme}: any): string => {
  if (id === selectedId) {
    return theme.color.purple100;
  }

  return theme.color.grey120;
};

const StyledTab = styled(Tab)`
  background: ${({theme}): string => theme.color.white};
  border-bottom: ${(props): string | number => getTabBorder(props)};
  border-width: 0 0 3px 0;
  color: ${(props): string => getTabColor(props)};
  cursor: pointer;
  font-size: 15px;
  font-weight: normal;
  height: 18px;
  margin: 0 5px -1px 0;
  padding: 0 40px 25px 0;
  text-align: left;
  transition: color 0.1s ease-in, border-width 0.1s ease-in;
  &:hover {
    color: ${({theme}): string => theme.color.purple100};
    border-bottom: 3px solid ${({theme}): string => theme.color.purple100};
  }
`;

const StyledTabPanel = styled(TabPanel)`
  padding-top: 10px;
  &:focus {
    outline: none;
  }
`;

const StyledTabList = styled(TabList)`
  padding-top: 10px;
`;

const TabDiv = styled.div`
  border-bottom: 1px solid ${({theme}): string => theme.color.grey80};
`;

type FormData = {
  code: string;
  priority: string;
  labels: {
    [key: string]: string;
  };
};

type Props = {
  locales: Locale[];
  uiLocales: Locale[];
  onSubmit: (event: React.FormEvent<HTMLFormElement>) => void;
  scopes: IndexedScopes;
  currentCatalogLocale: LocaleCode;
  handleDeleteAction: (lineNumber: number) => void;
  handleAddCondition: (condition: Condition) => void;
  handleDeleteCondition: (lineNumber: number) => void;
  conditions: (Condition | null)[];
  actions: (Action | null)[];
};

const EditRulesForm: React.FC<Props> = ({
  locales,
  uiLocales,
  onSubmit,
  scopes,
  currentCatalogLocale,
  actions,
  handleDeleteAction,
  conditions,
  handleAddCondition,
  handleDeleteCondition,
}) => {
  const translate = useTranslate();
  const tab = useTabState({selectedId: 'rulesBuilderTab'});

  const {formState} = useFormContext();
  const beforeUnload = (event: Event) => {
    event = event || window.event;
    if (formState.dirty) {
      const message = translate('pimee_catalog_rule.form.edit.discard_changes');
      event.returnValue = true;

      return message;
    }
    return;
  };
  window.onbeforeunload = beforeUnload;

  return (
    <form
      id='edit-rules-form'
      data-testid='edit-rules-form'
      onSubmit={onSubmit}>
      <StyledTabList
        {...tab}
        aria-label={translate('pimee_catalog_rule.form.tab.list')}>
        <TabDiv>
          <StyledTab {...tab} id='rulesBuilderTab' type='button'>
            {translate('pimee_catalog_rule.form.tab.rule_builder')}
          </StyledTab>
          <StyledTab {...tab} id='propertiesTab' type='button'>
            {translate('pim_common.properties')}
          </StyledTab>
        </TabDiv>
        <StyledTabPanel {...tab} tabIndex={-1}>
          <RulesBuilder
            currentCatalogLocale={currentCatalogLocale}
            locales={locales}
            uiLocales={uiLocales}
            scopes={scopes}
            actions={actions}
            handleDeleteAction={handleDeleteAction}
            conditions={conditions}
            handleAddCondition={handleAddCondition}
            handleDeleteCondition={handleDeleteCondition}
          />
        </StyledTabPanel>
        <StyledTabPanel {...tab} tabIndex={-1}>
          <RuleProperties locales={locales} />
        </StyledTabPanel>
      </StyledTabList>
    </form>
  );
};

export {EditRulesForm, FormData};
