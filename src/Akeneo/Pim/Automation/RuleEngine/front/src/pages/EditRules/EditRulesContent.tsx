import React from "react";
import { ThemeProvider } from "styled-components";
import * as akeneoTheme from "../../theme";
import {
  AkeneoSpinner,
  BreadcrumbItem,
  LastBreadcrumbItem,
  RulesHeader,
  Content,
} from "../../components";
import { EditRulesForm } from "./components/EditRulesForm";
import {
  generateAndRedirect,
  useBackboneRouter,
  useNotify,
  useTranslate,
  useUserContext,
} from "../../dependenciesTools/hooks";
import { Locale, RuleDefinition, getRuleDefinitionLabel } from "../../models";
import { useSubmitEditRuleForm } from "./hooks";
type Props = {
  ruleDefinitionCode: string;
  ruleDefinition: RuleDefinition;
  locales: Locale[];
};

const EditRulesContent: React.FC<Props> = ({
  ruleDefinitionCode,
  ruleDefinition,
  locales,
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const router = useBackboneRouter();
  const notify = useNotify();
  const currentCatalogLocale = userContext.get("catalogLocale");
  const [urlSettings, handleSettingsRoute] = generateAndRedirect(
    router,
    "pim_enrich_attribute_index"
  );
  const [urlRules, handleRulesRoute] = generateAndRedirect(
    router,
    "pimee_catalog_rule_rule_index"
  );
  const {
    formState,
    handleSubmit,
    register,
    pending,
  } = useSubmitEditRuleForm(
    ruleDefinitionCode,
    translate,
    notify,
    router,
    ruleDefinition,
    locales
  );

  return (
    <ThemeProvider theme={akeneoTheme}>
      {pending && <AkeneoSpinner />}
      <RulesHeader
        buttonLabel="pim_common.save"
        formId="edit-rules-form"
        title={getRuleDefinitionLabel(ruleDefinition, currentCatalogLocale)}
        translate={translate}
        unsavedChanges={formState.dirty}
      >
        <BreadcrumbItem href={`#${urlSettings}`} onClick={handleSettingsRoute}>
          {translate("pim_menu.tab.settings")}
        </BreadcrumbItem>
        <BreadcrumbItem href={`#${urlRules}`} onClick={handleRulesRoute}>
          {translate("pim_menu.item.rule")}
        </BreadcrumbItem>
        <LastBreadcrumbItem>{translate("pim_common.edit")}</LastBreadcrumbItem>
      </RulesHeader>
      <Content>
        <EditRulesForm
          register={register}
          onSubmit={handleSubmit}
          locales={locales}
          ruleDefinition={ruleDefinition}
          translate={translate}
        />
      </Content>
    </ThemeProvider>
  );
};

EditRulesContent.displayName = "EditRulesContent";

export { EditRulesContent };
