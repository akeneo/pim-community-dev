import React, {useEffect} from 'react';
import styled, {ThemeProvider} from 'styled-components';
import {DialogDisclosure, useDialogState} from 'reakit/Dialog';
import {FormContext} from 'react-hook-form';
import {AkeneoSpinner, Content, RulesHeader} from '../../components';
import {EditRulesForm} from './components/EditRulesForm';
import {
  generateAndRedirect,
  generateUrl,
  redirectToUrl,
  useBackboneRouter,
  useNotify,
  useTranslate,
  useUserContext,
} from '../../dependenciesTools/hooks';
import {Condition, Locale, RuleDefinition} from '../../models';
import {useSubmitEditRuleForm} from './hooks';
import {IndexedScopes} from '../../repositories/ScopeRepository';
import {AddActionButton} from './components/actions/AddActionButton';
import {Action} from '../../models/Action';
import {httpDelete} from '../../fetch';
import {NotificationLevel, Security} from '../../dependenciesTools';
import {Dropdown} from '../../components/Dropdown';
import {AlertDialog} from '../../components/AlertDialog/AlertDialog';
import {Breadcrumb, pimTheme} from 'akeneo-design-system';

const Illustration = styled.div`
  background-image: url('/bundles/akeneopimruleengine/assets/illustrations/rules.svg');
`;

type Props = {
  ruleDefinitionCode: string;
  ruleDefinition: RuleDefinition;
  locales: Locale[];
  uiLocales: Locale[];
  scopes: IndexedScopes;
  setIsDirty: (isDirty: boolean) => void;
  security: Security;
};

const EditRulesContent: React.FC<Props> = ({
  ruleDefinitionCode,
  ruleDefinition,
  locales,
  uiLocales,
  scopes,
  setIsDirty,
  security,
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const router = useBackboneRouter();
  const notify = useNotify();
  const currentCatalogLocale = userContext.get('catalogLocale');
  const [urlSettings] = generateAndRedirect(
    router,
    'pim_enrich_attribute_index'
  );
  const [urlRules] = generateAndRedirect(
    router,
    'pimee_catalog_rule_rule_index'
  );
  const {formMethods, onSubmit, pending} = useSubmitEditRuleForm(
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
    formMethods.watch(`labels.${currentCatalogLocale}`) ||
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

  const userHasExecutePermission = () =>
    security.isGranted('pimee_catalog_rule_rule_execute_permissions');

  const handleSaveAndExecuteRule = () => {
    formMethods.register({name: 'execute_on_save', value: true});
    onSubmit().then(() => {
      formMethods.unregister('execute_on_save');
    });
  };

  return (
    <ThemeProvider theme={pimTheme}>
      {(pending || deletePending) && <AkeneoSpinner />}
      <RulesHeader
        buttonLabel='pim_common.save'
        formId='edit-rules-form'
        title={title as string}
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
              label={translate(
                'pimee_catalog_rule.form.edit.actions.delete.label'
              )}
              description={translate(
                'pimee_catalog_rule.form.delete.description'
              )}
            />
            {userHasExecutePermission() && formMethods.watch('enabled') && (
              <>
                <DialogDisclosure
                  {...saveAndExecuteDialog}
                  className='AknDropdown-menuLink'>
                  {translate('pimee_catalog_rule.form.edit.execute.button')}
                </DialogDisclosure>
                <AlertDialog
                  dialog={saveAndExecuteDialog}
                  onValidate={handleSaveAndExecuteRule}
                  label={translate(
                    'pimee_catalog_rule.form.edit.execute.title'
                  )}
                  description={translate(
                    'pimee_catalog_rule.form.edit.execute.description'
                  )}
                  illustrationClassName={'AknFullPage-illustration--rules'}
                  validateButtonClassName={'AknButton--apply'}
                />
              </>
            )}
            <button
              type={'button'}
              className={'AknDropdown-menuLink'}
              onClick={event => {
                event.preventDefault();
                formMethods.register({
                  name: 'duplicate_on_save',
                  value: true,
                });
                onSubmit().then(() => {
                  formMethods.unregister('duplicate_on_save');
                });
              }}>
              {translate('pimee_catalog_rule.form.edit.duplicate.button')}
            </button>
          </Dropdown>
        }
        secondaryButton={<AddActionButton handleAddAction={handleAddAction} />}>
        <Breadcrumb>
          <Breadcrumb.Step href={`#${urlSettings}`}>
            {translate('pim_menu.tab.settings')}
          </Breadcrumb.Step>
          <Breadcrumb.Step href={`#${urlRules}`}>
            {translate('pim_menu.item.rule')}
          </Breadcrumb.Step>
          <Breadcrumb.Step>{translate('pim_common.edit')}</Breadcrumb.Step>
        </Breadcrumb>
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
                href='https://help.akeneo.com/pim/serenity/articles/manage-your-rules.html'
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
            uiLocales={uiLocales}
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

export {EditRulesContent};
