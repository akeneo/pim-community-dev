import React from "react";
import { ThemeProvider } from "styled-components";
import { useTabState, Tab, TabList, TabPanel } from "reakit/Tab";
import styled from "styled-components";
import * as akeneoTheme from "../../theme";
import { Content } from "../../template/Content";
import { RulesBuilder } from "./components/RulesBuilder";
import { RuleProperties } from "./components/RuleProperties";
import {
  BreadcrumbItem,
  LastBreadcrumbItem
} from "../../components/Breadcrumb";
import {
  useBackboneRouter,
  useTranslate,
  generateAndRedirect,
  useUserContext
} from "../../dependenciesTools/hooks";
import { RulesHeader } from "../../components/RulesHeader";
import {
  getRuleDefinitionLabel,
  RuleDefinition
} from "../../models/RuleDefinition";
import { getRuleDefinitionByCode } from "../../fetch/RuleDefinitionFetcher";
import { getActivatedLocales } from "../../fetch/LocaleFetcher";
import { Locale } from "../../models/Locale";

type Props = {
  ruleDefinitionCode: string;
};

const getTabBorder = ({ id, selectedId, theme }: any) => {
  if (id === selectedId) {
    return `3px solid ${theme.color.purple100}`;
  }
  return 0;
};

const getTabColor = ({ id, selectedId, theme }: any) => {
  if (id === selectedId) {
    return theme.color.purple100;
  }

  return theme.color.grey120;
}

const StyledTab = styled(Tab)`
  background: ${({ theme }) => theme.color.white};
  border-bottom: ${props => getTabBorder(props)};
  border-width: 0 0 3px 0;
  border-width: 0 0 3px 0;
  color: ${props => getTabColor(props)};
  font-size: 15px;
  font-weight: normal;
  height: 18px;
  padding: 0 40px 25px 0;
  text-align: left;
  cursor: pointer;
  margin: 0 5px -1px 0;
  transition: color 0.1s ease-in, border-width 0.1s ease-in;
  &:hover {
    color: ${({ theme }) => theme.color.purple100};
    border-bottom: 3px solid ${({ theme }) => theme.color.purple100};
  }
`;

const StyledTabPanel = styled(TabPanel)`
  padding-top: 20px;
`;

const StyledTabList = styled(TabList)`
  padding-top: 10px;
`;

const TabDiv = styled.div`
  border-bottom: 1px solid ${({ theme }) => theme.color.grey80};
`;

const EditRules: React.FC<Props> = ({ ruleDefinitionCode }) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const router = useBackboneRouter();
  const [ruleDefinition, setRuleDefinition] = React.useState<RuleDefinition>();
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

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    console.log("handleSubmit");
  };

  React.useEffect(() => {
    getRuleDefinitionByCode(ruleDefinitionCode, router)
      .then(ruleDefinition => {
        setRuleDefinition(ruleDefinition);
      })
      .catch(exception => {
        setIsError(true);
        console.error(exception);
      });

    getActivatedLocales(router)
      .then((locales: Locale[]) => {
        setLocales(locales);
      })
      .catch(exception => {
        setIsError(true);
        console.error(exception);
      });
  }, []);

  const currentCatalogLocale = userContext.get("catalogLocale");
  const tab = useTabState({ selectedId: "rulesBuilderTab" });

  return (
    <ThemeProvider theme={akeneoTheme}>
      {isError ? (
        "There was an error (TODO: better display)"
      ) : !ruleDefinition ? (
        "Loading (TODO: better display)"
      ) : (
        <>
          <RulesHeader
            title={getRuleDefinitionLabel(ruleDefinition, currentCatalogLocale)}
            formId="edit-rules-form"
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
            <form id="rules-builder-form" onSubmit={handleSubmit}>
              <StyledTabList {...tab}>
                <TabDiv>
                  <StyledTab {...tab} id="rulesBuilderTab">
                    {translate("pimee_catalog_rule.form.tab.rule_builder")}
                  </StyledTab>
                  <StyledTab {...tab} id="propertiesTab">
                    {translate("pim_common.properties")}
                  </StyledTab>
                </TabDiv>
                <StyledTabPanel {...tab}>
                  <RulesBuilder
                    translate={translate}
                    ruleDefinition={ruleDefinition}
                  />
                </StyledTabPanel>
                <StyledTabPanel {...tab}>
                  <RuleProperties
                    locales={locales}
                    ruleDefinition={ruleDefinition}
                    setRuleDefinition={setRuleDefinition}
                    translate={translate}
                  />
                </StyledTabPanel>
              </StyledTabList>
            </form>
          </Content>
        </>
      )}
    </ThemeProvider>
  );
};

EditRules.displayName = "EditRules";

export { EditRules };
