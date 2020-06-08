import { omit } from 'lodash';
import { Payload } from '../../../rules.types';
import { httpPut } from '../../../fetch';
import { generateUrl } from '../../../dependenciesTools/hooks';
import { FormData } from '../edit-rules.types';
import { useForm, DeepPartial } from 'react-hook-form';
import { Condition, Locale, RuleDefinition } from '../../../models';
import {
  Router,
  Translate,
  Notify,
  NotificationLevel,
} from '../../../dependenciesTools';
import { Action } from '../../../models/Action';

type Reset = (
  values?: DeepPartial<FormData>,
  omitResetState?: Partial<{
    errors: boolean;
    dirty: boolean;
    dirtyFields: boolean;
    isSubmitted: boolean;
    touched: boolean;
    isValid: boolean;
    submitCount: boolean;
  }>
) => void;

const transformFormData = (formData: FormData): Payload => {
  const filledConditions = formData?.content?.conditions
    ? formData.content.conditions.filter((condition: Condition | null) => {
        return condition !== null;
      })
    : [];

  const filledActions = formData?.content?.actions
    ? formData.content.actions.filter((action: Action | null) => {
        return action !== null;
      })
    : [];

  return {
    ...formData,
    priority: Number(formData.priority),
    content: {
      ...formData.content,
      conditions: filledConditions,
      actions: filledActions,
    },
  };
};

const submitEditRuleForm = (
  ruleDefinitionCode: string,
  translate: Translate,
  notify: Notify,
  router: Router,
  reset: Reset,
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
      /*const json = await response.json();
      const ruleDefinition = await denormalize(json, router);
      setRuleDefinition(ruleDefinition);*/
    } else {
      notify(
        NotificationLevel.ERROR,
        translate('pimee_catalog_rule.form.edit.notification.failed')
      );
    }
    console.log('Reset', formData)
    reset(formData);
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
    conditions: ruleDefinition.conditions || [],
    actions: ruleDefinition.actions.map((action: Action) => {
      if (Object.prototype.hasOwnProperty.call(action, 'json')) {
        // It's a FallbackAction
        return (action as { json: any }).json;
      }
      return omit(action, 'module');
    }),
  },
});

const useSubmitEditRuleForm = (
  ruleDefinitionCode: string,
  translate: Translate,
  notify: Notify,
  router: Router,
  ruleDefinition: RuleDefinition,
  locales: Locale[],
) => {
  const defaultValues = createFormDefaultValues(ruleDefinition, locales);
  const formMethods = useForm<FormData>({
    defaultValues,
  });
  const onSubmit = submitEditRuleForm(
    ruleDefinitionCode,
    translate,
    notify,
    router,
    formMethods.reset,
  );

  return {
    onSubmit: formMethods.handleSubmit(onSubmit),
    formMethods,
    pending: formMethods.formState.isSubmitting,
  };
};

export { useSubmitEditRuleForm };
