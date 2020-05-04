import React from 'react';
import styled from 'styled-components';
import { useTabState, Tab, TabList, TabPanel } from 'reakit/Tab';
import { Router, Translate } from '../../../dependenciesTools';
import { RuleDefinition } from '../../../models';
import { RulesBuilder } from './RulesBuilder';
import { RuleProperties } from './RuleProperties';
import { Locale } from '../../../models';
import { IndexedScopes } from '../../../fetch/ScopeFetcher';

const getTabBorder = ({ id, selectedId, theme }: any): string | number => {
  if (id === selectedId) {
    return `3px solid ${theme.color.purple100}`;
  }
  return 0;
};

const getTabColor = ({ id, selectedId, theme }: any): string => {
  if (id === selectedId) {
    return theme.color.purple100;
  }

  return theme.color.grey120;
};

const StyledTab = styled(Tab)`
  background: ${({ theme }): string => theme.color.white};
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
    color: ${({ theme }): string => theme.color.purple100};
    border-bottom: 3px solid ${({ theme }): string => theme.color.purple100};
  }
`;

const StyledTabPanel = styled(TabPanel)`
  padding-top: 20px;
`;

const StyledTabList = styled(TabList)`
  padding-top: 10px;
`;

const TabDiv = styled.div`
  border-bottom: 1px solid ${({ theme }): string => theme.color.grey80};
`;

type FormData = {
  code: string;
  priority: string;
  labels: {
    [key: string]: string;
  };
};

type Props = {
  onSubmit: (event: React.FormEvent<HTMLFormElement>) => void;
  translate: Translate;
  locales: Locale[];
  ruleDefinition: RuleDefinition;
  scopes: IndexedScopes;
  currentCatalogLocale: string;
  router: Router;
};

const EditRulesForm: React.FC<Props> = ({
  onSubmit,
  locales,
  ruleDefinition,
  translate,
  scopes,
  currentCatalogLocale,
  router,
}) => {
  const tab = useTabState({ selectedId: 'rulesBuilderTab' });
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
            ruleDefinition={ruleDefinition}
            translate={translate}
            locales={locales}
            scopes={scopes}
            currentCatalogLocale={currentCatalogLocale}
            router={router}
          />
        </StyledTabPanel>
        <StyledTabPanel {...tab} tabIndex={-1}>
          <RuleProperties locales={locales} translate={translate} />
        </StyledTabPanel>
      </StyledTabList>
    </form>
  );
};

export { EditRulesForm, FormData };
