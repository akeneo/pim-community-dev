import React from 'react';
import { Payload } from '../../../rules.types';
import { httpGet, httpPut } from '../../../fetch';
import { generateUrl, redirectToUrl } from '../../../dependenciesTools/hooks';
import { FormData } from '../edit-rules.types';
import { Control, FormContextValues, useForm } from 'react-hook-form';
import { Condition, Locale, RuleDefinition } from '../../../models';
import {
  NotificationLevel,
  Notify,
  Router,
  Translate,
} from '../../../dependenciesTools';
import { Action } from '../../../models/Action';
import {
  formatDateLocaleTimeConditionsFromBackend,
  formatDateLocaleTimeConditionsToBackend,
} from '../components/conditions/DateConditionLines/dateConditionLines.utils';
import { getErrorPath } from './ErrorPathResolver';

const registerConditions = (
  register: Control['register'],
  conditions: Condition[]
) => {
  if (conditions?.length) {
    conditions.forEach((_, index) => {
      register({ name: `content.conditions[${index}].field`, type: 'custom' });
      register({
        name: `content.conditions[${index}].operator`,
        type: 'custom',
      });
      register({ name: `content.conditions[${index}].value`, type: 'custom' });
      register({ name: `content.conditions[${index}].scope`, type: 'custom' });
      register({ name: `content.conditions[${index}].locale`, type: 'custom' });
    });
  }
};

const registerActions = (register: Control['register'], actions: Action[]) => {
  if (actions?.length) {
    actions.forEach((action, index) => {
      register({ name: `content.actions[${index}].type`, type: 'custom' });

      let fields: string[] = [];
      switch (action.type) {
        case 'add':
          fields = ['field', 'items', 'scope', 'locale'];
          break;
        case 'calculate':
          fields = [
            'destination.field',
            'destination.scope',
            'destination.locale',
            'destination.currency',
            'destination.unit',
            'round_precision',
          ];
          break;
        case 'clear':
          fields = ['field', 'scope', 'locale'];
          break;
        case 'concatenate':
          fields = [
            'to.field',
            'to.scope',
            'to.locale',
            'to.currency',
            'to.unit',
          ];
          break;
        case 'copy':
          fields = [
            'from_field',
            'from_scope',
            'from_locale',
            'to_field',
            'to_scope',
            'to_locale',
          ];
          break;
        case 'remove':
          fields = ['field', 'items', 'scope', 'locale', 'include_children'];
          break;
        case 'set':
          fields = ['field', 'scope', 'locale', 'value'];
          break;
        default:
          // Custom action. We need to register all the action keys.
          fields = Object.keys(action);
          break;
      }

      fields.forEach(field => {
        register({
          name: `content.actions[${index}].${field}`,
          type: 'custom',
        });
      });
    });
  }
};

const transformFormData = (formData: FormData): Payload => {
  const conditions = formatDateLocaleTimeConditionsToBackend(
    formData?.content?.conditions ?? []
  );
  const actions = formData?.content?.actions ?? [];
  return {
    code: formData.code,
    labels: formData.labels,
    priority: Number(formData.priority),
    enabled: formData.enabled,
    content: {
      conditions: conditions.filter(
        condition => typeof condition?.field === 'string'
      ),
      actions: actions
        .filter(action => typeof action?.type === 'string')
        .map((action: any) => {
          if (
            action.type === 'calculate' &&
            Array.isArray(action.full_operation_list)
          ) {
            [
              action.source,
              ...action.operation_list
            ] = action.full_operation_list;
            delete action.full_operation_list;
          }

          return action;
        }),
    },
  };
};

const doExecuteOnSave = async (
  router: Router,
  translate: Translate,
  notify: Notify,
  code: string
) => {
  const executeRuleUrl = generateUrl(
    router,
    'pimee_catalog_rule_rule_execute',
    { code }
  );
  const executeResponse = await httpGet(executeRuleUrl);
  if (executeResponse.ok) {
    notify(
      NotificationLevel.SUCCESS,
      translate('pimee_catalog_rule.form.edit.notification.execute_success')
    );
  } else {
    notify(
      NotificationLevel.ERROR,
      translate('pimee_catalog_rule.form.edit.notification.execute_failed')
    );
  }
};

const doDuplicateOnSave = (router: Router, originalRuleCode: string) => {
  const duplicateRuleUrl = generateUrl(router, 'pimee_catalog_rule_new', {
    originalRuleCode,
  });
  redirectToUrl(router, duplicateRuleUrl);
};

const createCalculateDefaultValues = (formData: FormData): FormData => {
  if (formData.content && formData.content.actions) {
    formData.content.actions = formData.content.actions.map((action: any) => {
      if (action && action.type === 'calculate') {
        action.full_operation_list = [action.source, ...action.operation_list];
      }

      return action;
    });
  }

  return formData;
};

const submitEditRuleForm = (
  ruleDefinitionCode: string,
  translate: Translate,
  notify: Notify,
  router: Router,
  reset: FormContextValues['reset'],
  register: Control['register'],
  setError: any
) => {
  return async (formData: FormData, event?: React.BaseSyntheticEvent) => {
    if (event) {
      event.preventDefault();
    }

    const executeOnSave = Object.prototype.hasOwnProperty.call(
      formData,
      'execute_on_save'
    );
    const duplicateOnSave = Object.prototype.hasOwnProperty.call(
      formData,
      'duplicate_on_save'
    );
    const updateRuleUrl = generateUrl(
      router,
      'pimee_enrich_rule_definition_update',
      { ruleDefinitionCode }
    );
    const updateResponse = await httpPut(updateRuleUrl, {
      body: transformFormData(formData),
    });
    if (updateResponse.ok) {
      formData = createCalculateDefaultValues(formData);
      reset(formData);
      registerConditions(register, formData.content?.conditions || []);
      registerActions(register, formData.content?.actions || []);
      if (executeOnSave) {
        doExecuteOnSave(router, translate, notify, ruleDefinitionCode);
      } else if (duplicateOnSave) {
        doDuplicateOnSave(router, ruleDefinitionCode);
      } else {
        notify(
          NotificationLevel.SUCCESS,
          translate('pimee_catalog_rule.form.edit.notification.success')
        );
      }
    } else {
      const errors = await updateResponse.json();
      errors.forEach(
        (error: { global: boolean; message: string; path: string }) => {
          setError(
            getErrorPath(formData, error.path),
            'validate',
            error.message
          );
        }
      );

      notify(
        NotificationLevel.ERROR,
        translate('pimee_catalog_rule.form.edit.notification.failed')
      );
    }
  };
};

const createLocalesLabels = (ruleDefinition: RuleDefinition) => (
  labels: { [key: string]: string },
  locale: Locale
) => ({
  ...labels,
  [locale.code]: ruleDefinition.labels[locale.code],
});

const createFormDefaultValues = (
  ruleDefinition: RuleDefinition,
  locales: Locale[]
): FormData => ({
  code: ruleDefinition.code,
  priority: ruleDefinition.priority.toString(),
  enabled: ruleDefinition.enabled,
  labels: locales.reduce(createLocalesLabels(ruleDefinition), {}),
  content: {
    conditions: formatDateLocaleTimeConditionsFromBackend(
      ruleDefinition.conditions || []
    ),
    actions: ruleDefinition.actions || [],
  },
});

const useSubmitEditRuleForm = (
  ruleDefinitionCode: string,
  translate: Translate,
  notify: Notify,
  router: Router,
  ruleDefinition: RuleDefinition,
  locales: Locale[]
) => {
  const defaultValues = createCalculateDefaultValues(
    createFormDefaultValues(ruleDefinition, locales)
  );
  const formMethods = useForm<FormData>({
    defaultValues,
  });
  const { reset, register, handleSubmit, setError } = formMethods;
  React.useEffect(() => {
    registerConditions(register, ruleDefinition.conditions);
    registerActions(register, ruleDefinition.actions);
  }, []);
  const onSubmit = submitEditRuleForm(
    ruleDefinitionCode,
    translate,
    notify,
    router,
    reset,
    register,
    setError
  );

  return {
    onSubmit: handleSubmit(onSubmit),
    formMethods,
    pending: formMethods.formState.isSubmitting,
  };
};

export { useSubmitEditRuleForm };
