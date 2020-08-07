import React from 'react';
import { Payload } from '../../../rules.types';
import { httpGet, httpPut } from '../../../fetch';
import { generateUrl } from '../../../dependenciesTools/hooks';
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
      register({ name: `content.actions[${index}].field`, type: 'custom' });
      register({ name: `content.actions[${index}].items`, type: 'custom' });
      register({ name: `content.actions[${index}].type`, type: 'custom' });
      register({ name: `content.actions[${index}].value`, type: 'custom' });
      register({ name: `content.actions[${index}].scope`, type: 'custom' });
      register({ name: `content.actions[${index}].locale`, type: 'custom' });
      register({
        name: `content.actions[${index}].from_field`,
        type: 'custom',
      });
      register({ name: `content.actions[${index}].to_field`, type: 'custom' });
      register({
        name: `content.actions[${index}].from_locale`,
        type: 'custom',
      });
      register({ name: `content.actions[${index}].to_locale`, type: 'custom' });
      register({
        name: `content.actions[${index}].from_scope`,
        type: 'custom',
      });
      register({ name: `content.actions[${index}].to_scope`, type: 'custom' });
      register({
        name: `content.actions[${index}].include_children`,
        type: 'custom',
      });

      if (typeof action.destination !== 'undefined') {
        ['field', 'scope', 'locale', 'unit'].forEach(key =>
          register({
            name: `content.actions[${index}].destination.${key}`,
            type: 'custom',
          })
        );
      }
      register({
        name: `content.actions[${index}].source`,
        type: 'custom',
      });
      if (typeof action.source !== 'undefined') {
        ['field', 'value', 'scope', 'locale', 'currency'].forEach(key =>
          register({
            name: `content.actions[${index}].source.${key}`,
            type: 'custom',
          })
        );
      }
      register({
        name: `content.actions[${index}].operation_list`,
        type: 'custom',
      });
      if (typeof action.operation_list !== 'undefined') {
        action.operation_list.map((_operation: any, operationIndex: number) => {
          ['field', 'value', 'scope', 'locale', 'currency', 'operator'].forEach(
            key =>
              register({
                name: `content.actions[${index}].operation_list[${operationIndex}].${key}`,
                type: 'custom',
              })
          );
        });
      }
      register({
        name: `content.actions[${index}].round_precision`,
        type: 'custom',
      });
    });
  }
};

const filterDataContentValues = (value: object) => {
  return (
    value !== null &&
    Object.keys(value).length &&
    Object.values(value).some(value => value != null)
  );
};

const transformFormData = (formData: FormData): Payload => {
  let conditions = formData?.content?.conditions;
  if (conditions) {
    conditions = formatDateLocaleTimeConditionsToBackend(conditions);
  }
  return {
    code: formData.code,
    labels: formData.labels,
    priority: Number(formData.priority),
    content: {
      conditions: conditions?.filter(filterDataContentValues) ?? [],
      actions:
        formData?.content?.actions?.filter(filterDataContentValues) ?? [],
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
    const updateRuleUrl = generateUrl(
      router,
      'pimee_enrich_rule_definition_update',
      { ruleDefinitionCode }
    );
    const updateResponse = await httpPut(updateRuleUrl, {
      body: transformFormData(formData),
    });
    if (updateResponse.ok) {
      reset(formData);
      registerConditions(register, formData.content?.conditions || []);
      registerActions(register, formData.content?.actions || []);

      if (executeOnSave) {
        doExecuteOnSave(router, translate, notify, ruleDefinitionCode);
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
  const defaultValues = createFormDefaultValues(ruleDefinition, locales);
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
