import React from "react";
import { ThemeProvider } from "styled-components";
import * as akeneoTheme from "../../theme";
import { Content } from "../../template/Content";
import { FlagLabel, InputText } from "../../components/InputText";
import { InputNumber } from "../../components/InputNumber";
import { SmallHelper } from "../../components/SmallHelper";
import {
  BreadcrumbItem,
  LastBreadcrumbItem,
} from "../../components/Breadcrumb";
import {
  useBackboneRouter,
  useTranslate,
  generateAndRedirect,
  useUserContext,
} from "../../dependenciesTools/hooks";
import { RulesHeader } from "../../components/RulesHeader";
import {getRuleDefinitionLabel, RuleDefinition} from "../../models/RuleDefinition";
import {getRuleDefinitionByCode} from "../../fetch/RuleDefinitionFetcher";
import {getActivatedLocales} from "../../fetch/LocaleFetcher";
import {Locale} from "../../models/Locale";

type Props = {
  ruleDefinitionCode: string
};

const EditRules: React.FC<Props> = ({ ruleDefinitionCode }) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const router = useBackboneRouter();

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
  };

  const [urlSettings, handleSettingsRoute] = generateAndRedirect(
    router,
    "pim_enrich_attribute_index"
  );
  const [urlRules, handleRulesRoute] = generateAndRedirect(
    router,
    "pimee_catalog_rule_rule_index"
  );

  const [ruleDefinition, setRuleDefinition] = React.useState<RuleDefinition>();
  const [isError, setIsError] = React.useState<boolean>(false);
  const [locales, setLocales] = React.useState<Locale[]>();

  React.useEffect(() => {
    getRuleDefinitionByCode(ruleDefinitionCode, router).then((ruleDefinition) => {
      setRuleDefinition(ruleDefinition);
    }).catch((exception) => {
      setIsError(true);
      console.error(exception);
    });

    getActivatedLocales(router).then((locales: Locale[]) => {
      setLocales(locales)
    }).catch((exception) => {
      setIsError(true);
      console.error(exception);
    });
  }, []);

  const currentCatalogLocale = userContext.get('catalogLocale');

  return (
    <ThemeProvider theme={akeneoTheme}>
      {isError ? 'There was an error (TODO: better display)' :
        !(ruleDefinition && locales) ? 'Loading (TODO: better display' :
          <form id="edit-rules-form" onSubmit={handleSubmit}>
            <RulesHeader title={getRuleDefinitionLabel(ruleDefinition, currentCatalogLocale)}>
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
                <div className="AknFieldContainer">
                  <InputText
                    disabled
                    id="edit-rules-input-code"
                    label={translate("pim_common.code")}
                    readOnly
                    value={ruleDefinition.code}
                  />
                </div>
                <div className="AknFieldContainer">
                  <InputNumber
                    id="edit-rules-input-priority"
                    label={translate('pimee_catalog_rule.form.edit.priority.label')}
                    value={ruleDefinition.priority}
                    onChange={(event) => {
                      setRuleDefinition({...ruleDefinition, priority: Number(event.target.value)});
                    }}
                  />
                </div>
                {locales.map((locale) => {
                  return <div className="AknFieldContainer" key={locale.code}>
                      <InputText
                        id={`edit-rules-input-label-${locale.code}`}
                        label={locale.label}
                        value={ruleDefinition.labels[locale.code]}
                        onChange={(event) => {
                          setRuleDefinition({...ruleDefinition, labels:
                              {...(ruleDefinition.labels), [locale.code]: event.target.value}
                          })
                        }}
                      >
                      <FlagLabel
                          locale={locale.code}
                          label={locale.label}
                          flagDescription={locale.label}
                      />
                    </InputText>
                  </div>
                })}
              </div>
            </Content>
          </form>
      }
    </ThemeProvider>
  );
};

EditRules.displayName = "EditRules";

export { EditRules };
