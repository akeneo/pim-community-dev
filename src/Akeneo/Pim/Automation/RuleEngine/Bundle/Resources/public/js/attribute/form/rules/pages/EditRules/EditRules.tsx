import React from "react";
import { ThemeProvider } from "styled-components";
import * as akeneoTheme from "../../theme";

import { Content } from "../../template/Content";
import { InputText } from "../../components/InputText";
import { SmallHelper } from "../../components/SmallHelper";
import {
  BreadcrumbItem,
  LastBreadcrumbItem
} from "../../components/Breadcrumb";
import {
  useRouterLegacyRedirect,
  useTranslate
} from "../../dependenciesTools/hooks";
import { RulesHeader } from "../../components/RulesHeader";

const EditRules: React.FC = () => {
  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
  };
  const [urlSettings, handleSettingsRoute] = useRouterLegacyRedirect(
    "pim_enrich_attribute_index"
  );
  const [urlRules, handleRulesRoute] = useRouterLegacyRedirect(
    "pimee_catalog_rule_rule_index"
  );
  return (
    <ThemeProvider theme={akeneoTheme}>
      <form id="edit-rules-form" onSubmit={handleSubmit}>
        <RulesHeader title="Set entity">
          <BreadcrumbItem href={urlSettings} onClick={handleSettingsRoute}>
            {useTranslate("pim_menu.tab.settings")}
          </BreadcrumbItem>
          <BreadcrumbItem href={urlRules} onClick={handleRulesRoute}>
            {useTranslate("pim_menu.item.rule")}
          </BreadcrumbItem>
          <LastBreadcrumbItem>
            {useTranslate("pim_common.edit")}
          </LastBreadcrumbItem>
        </RulesHeader>
        <Content>
          <div className="AknSubsection-title AknSubsection-title--glued">
            {useTranslate("pim_enrich.entity.attribute.module.edit.common")}
          </div>
          <SmallHelper>Page under construction</SmallHelper>
          <div className="AknFormContainer">
            <InputText
              disabled
              id="edit-rules-input-code"
              label={useTranslate("pim_common.code")}
              readOnly
            />
          </div>
        </Content>
      </form>
    </ThemeProvider>
  );
};

EditRules.displayName = "EditRules";

export { EditRules };
