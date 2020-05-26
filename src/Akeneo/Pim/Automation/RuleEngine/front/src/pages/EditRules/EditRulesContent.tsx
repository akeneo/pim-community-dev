import React, { useEffect } from 'react';
import { ThemeProvider } from 'styled-components';
import { FormContext } from 'react-hook-form';

import * as akeneoTheme from '../../theme';
import {
  AkeneoSpinner,
  BreadcrumbItem,
  LastBreadcrumbItem,
  RulesHeader,
  Content,
} from '../../components';
import { EditRulesForm } from './components/EditRulesForm';
import {
  generateAndRedirect,
  useBackboneRouter,
  useNotify,
  useTranslate,
  useUserContext,
} from '../../dependenciesTools/hooks';
import { Locale, RuleDefinition } from '../../models';
import { useSubmitEditRuleForm } from './hooks';
import { IndexedScopes } from '../../repositories/ScopeRepository';

type Props = {
  ruleDefinitionCode: string;
  ruleDefinition: RuleDefinition;
  locales: Locale[];
  scopes: IndexedScopes;
  setIsDirty: (isDirty: boolean) => void;
  setRuleDefinition: (ruleDefinition: RuleDefinition) => void;
};

const EditRulesContent: React.FC<Props> = ({
  ruleDefinitionCode,
  ruleDefinition,
  locales,
  scopes,
  setIsDirty,
  setRuleDefinition,
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const router = useBackboneRouter();
  const notify = useNotify();
  const currentCatalogLocale = userContext.get('catalogLocale');
  const [urlSettings, handleSettingsRoute] = generateAndRedirect(
    router,
    'pim_enrich_attribute_index'
  );
  const [urlRules, handleRulesRoute] = generateAndRedirect(
    router,
    'pimee_catalog_rule_rule_index'
  );
  const { formMethods, onSubmit, pending } = useSubmitEditRuleForm(
    ruleDefinitionCode,
    translate,
    notify,
    router,
    ruleDefinition,
    locales,
    setRuleDefinition
  );

  useEffect(() => {
    setIsDirty(formMethods.formState.dirty);
  }, [formMethods.formState.dirty]);

  const title =
    (formMethods.watch(`labels.${currentCatalogLocale}`) as string) ||
    `[${ruleDefinitionCode}]`;

  return (
    <ThemeProvider theme={akeneoTheme}>
      {pending && <AkeneoSpinner />}
      <RulesHeader
        buttonLabel='pim_common.save'
        formId='edit-rules-form'
        title={title}
        translate={translate}
        unsavedChanges={formMethods.formState.dirty}>
        <BreadcrumbItem href={`#${urlSettings}`} onClick={handleSettingsRoute}>
          {translate('pim_menu.tab.settings')}
        </BreadcrumbItem>
        <BreadcrumbItem href={`#${urlRules}`} onClick={handleRulesRoute}>
          {translate('pim_menu.item.rule')}
        </BreadcrumbItem>
        <LastBreadcrumbItem>{translate('pim_common.edit')}</LastBreadcrumbItem>
      </RulesHeader>
      <Content>
        <FormContext {...formMethods}>
          <EditRulesForm
            onSubmit={onSubmit}
            locales={locales}
            ruleDefinition={ruleDefinition}
            translate={translate}
            scopes={scopes}
            currentCatalogLocale={currentCatalogLocale}
            router={router}
          />
        </FormContext>
      </Content>
    </ThemeProvider>
  );
};

EditRulesContent.displayName = 'EditRulesContent';

export { EditRulesContent };
