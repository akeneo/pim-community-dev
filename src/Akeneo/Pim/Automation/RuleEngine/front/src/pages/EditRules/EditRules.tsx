import React from "react";
import { ThemeProvider } from "styled-components";
import * as akeneoTheme from "../../theme";
import { AkeneoSpinner } from "../../components/AkeneoSpinner";
import { Content } from "../../template/Content";
import { httpPut } from "../../fetch";
import {
  BreadcrumbItem,
  LastBreadcrumbItem,
} from "../../components/Breadcrumb";
import { EditRulesForm, FormData } from "./components/EditRulesForm";
import {
  generateAndRedirect,
  generateUrl,
  useBackboneRouter,
  useNotify,
  useTranslate,
  useUserContext,
  NotificationLevel,
} from "../../dependenciesTools/hooks";
import { RulesHeader } from "../../components/RulesHeader";
import {
  getRuleDefinitionLabel,
  RuleDefinition,
} from "../../models/RuleDefinition";
import { getRuleDefinitionByCode } from "../../fetch/RuleDefinitionFetcher";
import { getActivatedLocales } from "../../fetch/LocaleFetcher";
import { Locale } from "../../models/Locale";
import { Payload } from "../../rules.types";

type Props = {
  ruleDefinitionCode: string;
};

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

const EditRules: React.FC<Props> = ({ ruleDefinitionCode }) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const router = useBackboneRouter();
  const notify = useNotify();
  const [ruleDefinition, setRuleDefinition] = React.useState<RuleDefinition>();
  const [pending, setPending] = React.useState<boolean>(false);
  const [isError, setIsError] = React.useState<boolean>(false);
  const [locales, setLocales] = React.useState<Locale[]>();
  const [urlSettings, handleSettingsRoute] = generateAndRedirect(
    router,
    "pim_enrich_attribute_index"
  );
  const [urlRules, handleRulesRoute] = generateAndRedirect(
    router,
    "pimee_catalog_rule_rule_index"
  );

  React.useEffect(() => {
    getRuleDefinitionByCode(ruleDefinitionCode, router)
      .then((ruleDefinition) => {
        setRuleDefinition(ruleDefinition);
      })
      .catch((exception) => {
        setIsError(true);
        console.error(exception);
      });

    getActivatedLocales(router)
      .then((locales: Locale[]) => {
        setLocales(locales);
      })
      .catch((exception) => {
        setIsError(true);
        console.error(exception);
      });
  }, []);

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
    setPending(true);
    const response = await httpPut(updateRuleUrl, {
      body: transformFormData(formData),
    });
    if (response.ok) {
      notify(
        NotificationLevel.SUCCESS,
        translate("pimee_catalog_rule.form.edit.notification.success")
      );
    } else {
      notify(
        NotificationLevel.ERROR,
        translate("pimee_catalog_rule.form.edit.notification.failed")
      );
    }
    setPending(false);
  };
  const currentCatalogLocale = userContext.get("catalogLocale");
  return (
    <ThemeProvider theme={akeneoTheme}>
      {pending && <AkeneoSpinner />}
      {isError ? (
        "There was an error (TODO: better display)"
      ) : !ruleDefinition || !locales ? (
        "Loading (TODO: better display)"
      ) : (
        <>
          <RulesHeader
            title={getRuleDefinitionLabel(ruleDefinition, currentCatalogLocale)}
            formId="edit-rules-form"
            buttonLabel={translate("pim_common.save")}
          >
            <BreadcrumbItem
              href={`#${urlSettings}`}
              onClick={handleSettingsRoute}
            >
              {translate("pim_menu.tab.settings")}
            </BreadcrumbItem>
            <BreadcrumbItem href={`#${urlRules}`} onClick={handleRulesRoute}>
              {translate("pim_menu.item.rule")}
            </BreadcrumbItem>
            <LastBreadcrumbItem>
              {translate("pim_common.edit")}
            </LastBreadcrumbItem>
          </RulesHeader>
          <Content>
            <EditRulesForm
              onSubmit={onSubmit}
              locales={locales}
              ruleDefinition={ruleDefinition}
              setRuleDefinition={setRuleDefinition}
              translate={translate}
            />
          </Content>
        </>
      )}
    </ThemeProvider>
  );
};

EditRules.displayName = "EditRules";

export { EditRules };
