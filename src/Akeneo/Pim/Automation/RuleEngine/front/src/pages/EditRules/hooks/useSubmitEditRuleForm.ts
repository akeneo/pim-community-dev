import { Payload } from "../../../rules.types";
import { httpPut } from "../../../fetch";
import { generateUrl } from "../../../dependenciesTools/hooks";
import { FormData } from "../edit-rules.types";
import { useForm, DeepPartial } from "react-hook-form";
import { Locale, RuleDefinition } from "../../../models";
import {
  Router,
  Translate,
  Notify,
  NotificationLevel,
} from "../../../dependenciesTools";

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
  return {
    ...formData,
    priority: Number(formData.priority),
    content: {
      conditions: {},
      actions: {},
    },
  };
};

const submitEditRuleForm = (
  ruleDefinitionCode: string,
  translate: Translate,
  notify: Notify,
  router: Router,
  reset: Reset
) => {
  const onSubmit = async (
    formData: FormData,
    event: React.FormEvent<HTMLFormElement>
  ) => {
    event.preventDefault();
    const updateRuleUrl = generateUrl(
      router,
      "pimee_enrich_rule_definition_update",
      { ruleDefinitionCode }
    );
    const response = await httpPut(updateRuleUrl, {
      body: transformFormData(formData),
    });
    if (response.ok) {
      notify(
        NotificationLevel.SUCCESS,
        translate("pimee_catalog_rule.form.edit.notification.success")
      );
      reset(
        {
          ...formData,
        },
        {
          errors: false,
          dirtyFields: false,
          dirty: false,
          isSubmitted: false,
          touched: false,
          isValid: false,
          submitCount: false,
        }
      );
    } else {
      notify(
        NotificationLevel.ERROR,
        translate("pimee_catalog_rule.form.edit.notification.failed")
      );
    }
  };
  return onSubmit;
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
) => ({
  code: ruleDefinition.code,
  priority: ruleDefinition.priority.toString(),
  labels: locales.reduce(createLocalesLabels(ruleDefinition), {}),
  conditions: ruleDefinition.conditions,
  actions: ruleDefinition.actions
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
  const { handleSubmit, register, formState, reset } = useForm<FormData>({
    defaultValues,
  });
  const onSubmit = submitEditRuleForm(
    ruleDefinitionCode,
    translate,
    notify,
    router,
    reset
  );
  return {
    handleSubmit: handleSubmit(onSubmit),
    register,
    formState,
    pending: formState.isSubmitting,
  };
};

export { useSubmitEditRuleForm };
