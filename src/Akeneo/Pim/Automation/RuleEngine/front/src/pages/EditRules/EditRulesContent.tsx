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
  generateUrl,
  redirectToUrl,
} from '../../dependenciesTools/hooks';
import { Locale, RuleDefinition } from '../../models';
import { useSubmitEditRuleForm } from './hooks';
import { IndexedScopes } from '../../repositories/ScopeRepository';
import { AddActionButton } from './components/actions/AddActionButton';
import { Action } from '../../models/Action';
import { httpDelete } from '../../fetch';
import { NotificationLevel } from '../../dependenciesTools';

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
    locales
  );
  const [actionsState, setActionsState] = React.useState<(Action | null)[]>(
    ruleDefinition.actions
  );

  const [deletePending, setDeletePending] = React.useState(false);

  useEffect(() => {
    setIsDirty(!!formMethods.formState.dirtyFields.size);
  }, [formMethods.formState.dirtyFields.size]);

  const title =
    (formMethods.watch(`labels.${currentCatalogLocale}`) as string) ||
    `[${ruleDefinitionCode}]`;

  const append = (action: Action) => {
    setActionsState([...actionsState, action]);
  };

  const remove = (lineNumber: number) => {
    actionsState[lineNumber] = null;
    setActionsState([...actionsState]);
  };

  const handleAddAction = (action: any) => {
    append(action);
  };

  const handleDeleteRule = async (): Promise<any> => {
    const deleteRule = router.generate('pimee_catalog_rule_rule_delete', {
      id: ruleDefinition.id,
    });

    setDeletePending(true);

    let result: any;

    try {
      result = await httpDelete(deleteRule);
    } catch (error) {
      setDeletePending(false);
      notify(
        NotificationLevel.ERROR,
        translate('pimee_catalog_rule.form.delete.notification.failed')
      );

      return error;
    }

    if (result.ok) {
      notify(
        NotificationLevel.SUCCESS,
        translate('pimee_catalog_rule.form.delete.notification.success')
      );
      redirectToUrl(
        router,
        generateUrl(router, 'pimee_catalog_rule_rule_index')
      );
    } else {
      setDeletePending(false);
      notify(
        NotificationLevel.ERROR,
        translate('pimee_catalog_rule.form.delete.notification.failed')
      );
    }
    return result;
  };

  return (
    <ThemeProvider theme={akeneoTheme}>
      {(pending || deletePending) && <AkeneoSpinner />}
      <RulesHeader
        buttonLabel='pim_common.save'
        formId='edit-rules-form'
        title={title}
        handleDeleteRule={handleDeleteRule}
        unsavedChanges={formMethods.formState.dirty}
        secondaryButton={<AddActionButton handleAddAction={handleAddAction} />}>
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
            scopes={scopes}
            actions={actionsState}
            handleDeleteAction={remove}
            conditions={ruleDefinition.conditions}
          />
        </FormContext>
      </Content>
    </ThemeProvider>
  );
};

EditRulesContent.displayName = 'EditRulesContent';

export { EditRulesContent };
