import React from 'react';
import { Payload } from '../../../rules.types';
import { httpPut } from '../../../fetch';
import { generateUrl } from '../../../dependenciesTools/hooks';
import { FormData } from '../edit-rules.types';
import { useForm, FormContextValues, Control } from 'react-hook-form';
import { Locale, RuleDefinition, Condition } from '../../../models';
import {
  Router,
  Translate,
  Notify,
  NotificationLevel,
} from '../../../dependenciesTools';
import { Action } from '../../../models/Action';
import {
  formatDateLocaleTimeConditionsToBackend,
  formatDateLocaleTimeConditionsFromBackend,
} from '../components/conditions/DateConditionLines/dateConditionLines.utils';

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
    actions.forEach((_, index) => {
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
    ...formData,
    priority: Number(formData.priority),
    content: {
      conditions: conditions?.filter(filterDataContentValues) ?? [],
      actions:
        formData?.content?.actions?.filter(filterDataContentValues) ?? [],
    },
  };
};

const getErrorPath = (path: string) => {
  if (path.match(/^(actions|conditions)\[\d+\]$/g)) {
    /* The error path is not linked to a specific field (value, field, operator...)
     * As in react-hook-form, every error is linked to a field, we need to link it to a fake field. */
    return `content.${path}.__fromBackend__`;
  }
  return `content.${path}`;
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
    const updateRuleUrl = generateUrl(
      router,
      'pimee_enrich_rule_definition_update',
      { ruleDefinitionCode }
    );
    const response = await httpPut(updateRuleUrl, {
      body: transformFormData(formData),
    });
    if (response.ok) {
      notify(
        NotificationLevel.SUCCESS,
        translate('pimee_catalog_rule.form.edit.notification.success')
      );
      reset(formData);
      registerConditions(register, formData.content?.conditions || []);
      registerActions(register, formData.content?.actions || []);
    } else {
      const errors = await response.json();
      errors.forEach(
        (error: { global: boolean; message: string; path: string }) => {
          setError(getErrorPath(error.path), 'validate', error.message);
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
