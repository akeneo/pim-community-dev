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
import { AddActionButton } from './components/actions/AddActionButton';
import { Action } from '../../models/Action';

type Props = {
  ruleDefinitionCode: string;
  ruleDefinition: RuleDefinition;
  locales: Locale[];
  scopes: IndexedScopes;
  setIsDirty: (isDirty: boolean) => void;
};

const EditRulesContent: React.FC<Props> = ({
  ruleDefinitionCode,
  ruleDefinition,
  locales,
  scopes,
  setIsDirty,
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
  );

  useEffect(() => {
    setIsDirty(formMethods.formState.dirtyFields.size > 0);
  }, [formMethods.formState.dirtyFields]);

  const title =
    (formMethods.watch(`labels.${currentCatalogLocale}`) as string) ||
    `[${ruleDefinitionCode}]`;

  const [actions, setActions] = React.useState<(Action | null)[]>(
    ruleDefinition.actions
  );
  React.useEffect(() => {
    setActions(ruleDefinition.actions);
  }, [ruleDefinition]);

  const handleDeleteAction = (lineNumber: number) => {
    Object.keys(formMethods.getValues()).forEach((value: string) => {
      if (value.startsWith(`content.actions[${lineNumber}]`)) {
        formMethods.unregister(value);
        console.log(formMethods.getValues());
        console.log(formMethods.watch(value));
      }
    });
    setActions(
      actions.map((action: Action | null, i: number) => {
        return i === lineNumber ? null : action;
      })
    );
  };

  const handleAddAction = (action: Action) => {
    setActions([...actions, action]);
  };

  return (
    <ThemeProvider theme={akeneoTheme}>
      {pending && <AkeneoSpinner />}
      <RulesHeader
        buttonLabel='pim_common.save'
        formId='edit-rules-form'
        title={title}
        translate={translate}
        unsavedChanges={formMethods.formState.dirtyFields.size > 0}
        secondaryButton={
          <AddActionButton
            translate={translate}
            handleAddAction={handleAddAction}
          />
        }>
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
            currentCatalogLocale={currentCatalogLocale}
            locales={locales}
            onSubmit={onSubmit}
            ruleDefinition={ruleDefinition}
            scopes={scopes}
            translate={translate}
            actions={actions}
            handleDeleteAction={handleDeleteAction}
          />
        </FormContext>
      </Content>
    </ThemeProvider>
  );
};

EditRulesContent.displayName = 'EditRulesContent';

export { EditRulesContent };
