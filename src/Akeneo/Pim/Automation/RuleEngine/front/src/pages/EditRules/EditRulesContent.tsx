import React, { useEffect } from 'react';
import styled from 'styled-components';
import { useDialogState, DialogDisclosure } from 'reakit/Dialog';
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
import { Locale, RuleDefinition, Condition } from '../../models';
import { useSubmitEditRuleForm } from './hooks';
import { IndexedScopes } from '../../repositories/ScopeRepository';
import { AddActionButton } from './components/actions/AddActionButton';
import { Action } from '../../models/Action';
import { httpDelete } from '../../fetch';
import { NotificationLevel } from '../../dependenciesTools';
import { Dropdown } from '../../components/Dropdown';
import { AlertDialog } from '../../components/AlertDialog/AlertDialog';

const Illustration = styled.div`
  background-image: url('/bundles/akeneopimruleengine/assets/illustrations/rules.svg');
`;

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

  const [conditionsState, setConditionsState] = React.useState<
    (Condition | null)[]
  >(ruleDefinition.conditions);

  const [deletePending, setDeletePending] = React.useState(false);

  useEffect(() => {
    setIsDirty(!!formMethods.formState.dirtyFields.size);
  }, [formMethods.formState.dirtyFields.size]);

  const title =
    (formMethods.watch(`labels.${currentCatalogLocale}`) as string) ||
    `[${ruleDefinitionCode}]`;

  const appendAction = (action: Action) => {
    setActionsState([...actionsState, action]);
  };

  const removeAction = (lineNumber: number) => {
    actionsState[lineNumber] = null;
    setActionsState([...actionsState]);
  };

  const handleAddAction = (action: any) => {
    appendAction(action);
  };

  const deleteDialog = useDialogState();

  const appendCondition = (condition: Condition) => {
    setConditionsState([...conditionsState, condition]);
  };

  const removeCondition = (lineNumber: number) => {
    conditionsState[lineNumber] = null;
    setConditionsState([...conditionsState]);
  };

  const handleDeleteRule = async (): Promise<any> => {
    const deleteRuleUrl = router.generate('pimee_catalog_rule_rule_delete', {
      id: ruleDefinition.id,
    });

    setDeletePending(true);

    let result: any;

    try {
      result = await httpDelete(deleteRuleUrl);
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
      setIsDirty(false);
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

  const saveAndExecuteDialog = useDialogState();

  const handleSaveAndExecuteRule = () => {
    formMethods.register({ name: 'execute_on_save', value: true });
    onSubmit().then(() => {
      formMethods.unregister('execute_on_save');
    });
  };

  return (
    <ThemeProvider theme={akeneoTheme}>
      {(pending || deletePending) && <AkeneoSpinner />}
      <RulesHeader
        buttonLabel='pim_common.save'
        formId='edit-rules-form'
        title={title}
        unsavedChanges={formMethods.formState.dirty}
        saveable={!!actionsState.filter(Boolean).length}
        dropdown={
          <Dropdown title={translate('pim_common.other_actions')}>
            <DialogDisclosure
              {...deleteDialog}
              className='AknDropdown-menuLink'>
              {translate('pim_common.delete')}
            </DialogDisclosure>
            <AlertDialog
              dialog={deleteDialog}
              onValidate={handleDeleteRule}
              cancelLabel={translate('pim_common.cancel')}
              confirmLabel={translate('pim_common.confirm')}
              label={translate(
                'pimee_catalog_rule.form.edit.actions.delete.label'
              )}
              description={translate(
                'pimee_catalog_rule.form.delete.description'
              )}
            />
            <DialogDisclosure
              {...saveAndExecuteDialog}
              className='AknDropdown-menuLink'>
              {translate('pimee_catalog_rule.form.edit.execute.button')}
            </DialogDisclosure>
            <AlertDialog
              dialog={saveAndExecuteDialog}
              onValidate={handleSaveAndExecuteRule}
              cancelLabel={translate('pim_common.cancel')}
              confirmLabel={translate('pim_common.confirm')}
              label={translate('pimee_catalog_rule.form.edit.execute.title')}
              description={translate(
                'pimee_catalog_rule.form.edit.execute.description'
              )}
              illustrationClassName={'AknFullPage-illustration--rules'}
            />
          </Dropdown>
        }
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
        <div className='AknDescriptionHeader'>
          <Illustration className='AknDescriptionHeader-icon' />
          <div className='AknDescriptionHeader-title'>
            {translate('pimee_catalog_rule.form.edit.header.welcome')}
            <div className='AknDescriptionHeader-description'>
              {translate('pimee_catalog_rule.form.edit.header.description')}
              <br />
              <a
                href='https://help.akeneo.com/pim/serenity/articles/get-started-with-the-rules-engine.html'
                target='_blank'
                rel='noopener noreferrer'
                className='AknDescriptionHeader-link'>
                {translate(
                  'pimee_catalog_rule.form.edit.header.documentation_link'
                )}
              </a>
            </div>
          </div>
        </div>
        <FormContext {...formMethods}>
          <EditRulesForm
            currentCatalogLocale={currentCatalogLocale}
            locales={locales}
            onSubmit={onSubmit}
            scopes={scopes}
            actions={actionsState}
            handleDeleteAction={removeAction}
            conditions={conditionsState}
            handleAddCondition={appendCondition}
            handleDeleteCondition={removeCondition}
          />
        </FormContext>
      </Content>
    </ThemeProvider>
  );
};

EditRulesContent.displayName = 'EditRulesContent';

export { EditRulesContent };
