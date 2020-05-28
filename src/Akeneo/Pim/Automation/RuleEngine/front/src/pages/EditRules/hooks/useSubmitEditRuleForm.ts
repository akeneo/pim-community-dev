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
import { denormalize } from '../../../models/rule-definition-denormalizer';
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
  const filledConditions = formData.content
    ? formData.content.conditions.filter((condition: Condition | null) => {
        return condition !== null;
      })
    : [];

  return {
    ...formData,
    priority: Number(formData.priority),
    content: {
      ...formData.content,
      conditions: filledConditions,
      actions: (formData.content && formData.content.actions) || [],
    },
  };
};

const submitEditRuleForm = (
  ruleDefinitionCode: string,
  translate: Translate,
  notify: Notify,
  router: Router,
  reset: Reset,
  setRuleDefinition: (ruleDefinition: RuleDefinition) => void
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
      reset({ ...formData });
      const json = await response.json();
      const ruleDefinition = await denormalize(json, router);
      setRuleDefinition(ruleDefinition);
    } else {
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
  setRuleDefinition: (ruleDefinition: RuleDefinition) => void
) => {
  const defaultValues = createFormDefaultValues(ruleDefinition, locales);
  console.log(defaultValues);
  const formMethods = useForm<FormData>({
    defaultValues,
  });
  const onSubmit = submitEditRuleForm(
    ruleDefinitionCode,
    translate,
    notify,
    router,
    formMethods.reset,
    setRuleDefinition
  );
  return {
    onSubmit: formMethods.handleSubmit(onSubmit),
    formMethods,
    pending: formMethods.formState.isSubmitting,
  };
};

export { useSubmitEditRuleForm };
