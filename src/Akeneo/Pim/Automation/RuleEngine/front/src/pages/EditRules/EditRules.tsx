import React from "react";
import { ThemeProvider } from "styled-components";
import * as akeneoTheme from "../../theme";
import { Content } from "../../template/Content";
import { InputText } from "../../components/InputText";
import { SmallHelper } from "../../components/SmallHelper";
import {
  BreadcrumbItem,
  LastBreadcrumbItem,
} from "../../components/Breadcrumb";
import {
  useBackboneRouter,
  useTranslate,
  generateAndRedirect,
} from "../../dependenciesTools/hooks";
import { RulesHeader } from "../../components/RulesHeader";
import RuleDefinition from "../../models/RuleDefinition";
import {getByCode} from "../../fetchers/RuleDefinitionFetcher";

type Props = {
  ruleDefinitionCode: string
};

const EditRules: React.FC<Props> = ({ ruleDefinitionCode }) => {
  const translate = useTranslate();
  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
  };
  const router = useBackboneRouter();
  const [urlSettings, handleSettingsRoute] = generateAndRedirect(
    router,
    "pim_enrich_attribute_index"
  );
  const [urlRules, handleRulesRoute] = generateAndRedirect(
    router,
    "pimee_catalog_rule_rule_index"
  );
  const router = useLegacyRouter();

  const [ruleDefinition, setRuleDefinition] = React.useState<RuleDefinition>();

  React.useEffect(() => {
    getByCode(ruleDefinitionCode, router).then((ruleDefinition) => { setRuleDefinition(ruleDefinition); });
  }, []);

  return (
    <ThemeProvider theme={akeneoTheme}>
      <form id="edit-rules-form" onSubmit={handleSubmit}>
        <RulesHeader title="Set entity">
          <BreadcrumbItem href={`#${urlSettings}`} onClick={handleSettingsRoute}>
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
          <div className="AknSubsection-title AknSubsection-title--glued">
            {translate("pim_enrich.entity.attribute.module.edit.common")}
          </div>
          <SmallHelper>Page under construction</SmallHelper>
          <div className="AknFormContainer">
            <InputText
              disabled
              id="edit-rules-input-code"
              label={translate("pim_common.code")}
              readOnly
              value={ruleDefinition ? ruleDefinition.code : ''}
            />
          </div>
        </Content>
      </form>
    </ThemeProvider>
  );
};

EditRules.displayName = "EditRules";

export { EditRules };
