import React from "react";
import styled from "styled-components";
import { useTabState, Tab, TabList, TabPanel } from "reakit/Tab";
import { useForm } from "react-hook-form";
import { Translate } from "../../../dependenciesTools";
import { RuleDefinition } from "../../../models/RuleDefinition";
import { RulesBuilder } from "./RulesBuilder";
import { RuleProperties } from "./RuleProperties";
import { Locale } from "../../../models/Locale";

const getTabBorder = ({ id, selectedId, theme }: any) => {
  if (id === selectedId) {
    return `3px solid ${theme.color.purple100}`;
  }
  return 0;
};

const StyledTab = styled(Tab)`
  border-bottom: ${(props) => getTabBorder(props)};
  border-width: 0 0 3px 0;
  color: ${({ theme }) => theme.color.purple100};
  font-size: 15px;
  font-weight: normal;
  height: 18px;
  padding: 0 0 25px 0;
  text-align: left;
  width: 125px;
`;

const StyledTabPanel = styled(TabPanel)`
  padding-top: 40px;
`;

const StyledTabList = styled(TabList)`
  padding-top: 10px;
`;

const TabDiv = styled.div`
  border-bottom: 1px solid ${({ theme }) => theme.color.grey80};
`;

type FormData = {
  code: string;
  priority: string;
  labels: {
    [key: string]: string;
  };
};

type Props = {
  onSubmit: (
    formData: FormData,
    event: React.FormEvent<HTMLFormElement>
  ) => void;
  translate: Translate;
  locales: Locale[];
  ruleDefinition: RuleDefinition;
  setRuleDefinition: React.Dispatch<React.SetStateAction<RuleDefinition>>;
};

const transformLocalesToObject = (
  locales: Locale[],
  ruleDefinition: RuleDefinition
): { [key: string]: string } =>
  locales.reduce((acc, value) => {
    return {
      ...acc,
      [value.code]: ruleDefinition.labels[value.code],
    };
  }, {});

const createFormDefaultValues = (
  ruleDefinition: RuleDefinition,
  locales: Locale[]
) => ({
  code: ruleDefinition.code,
  priority: ruleDefinition.priority.toString(),
  labels: transformLocalesToObject(locales, ruleDefinition),
});

const EditRulesForm: React.FC<Props> = ({
  onSubmit,
  locales,
  ruleDefinition,
  setRuleDefinition,
  translate,
}) => {
  const tab = useTabState({ selectedId: "rulesBuilderTab" });
  const defaultValues = createFormDefaultValues(ruleDefinition, locales);
  const { handleSubmit, register } = useForm<FormData>({
    defaultValues,
  });
  return (
    <form
      id="edit-rules-form"
      data-testid="edit-rules-form"
      onSubmit={handleSubmit(onSubmit)}
    >
      <StyledTabList
        {...tab}
        aria-label={translate("pimee_catalog_rule.form.tab.list")}
      >
        <TabDiv>
          <StyledTab {...tab} id="rulesBuilderTab" type="button">
            {translate("pimee_catalog_rule.form.tab.rule_builder")}
          </StyledTab>
          <StyledTab {...tab} id="propertiesTab" type="button">
            {translate("pim_common.properties")}
          </StyledTab>
        </TabDiv>
        <StyledTabPanel {...tab} tabIndex={-1}>
          <RulesBuilder ruleDefinition={ruleDefinition} translate={translate} />
        </StyledTabPanel>
        <StyledTabPanel {...tab} tabIndex={-1}>
          <RuleProperties
            register={register}
            locales={locales}
            ruleDefinition={ruleDefinition}
            setRuleDefinition={setRuleDefinition}
            translate={translate}
          />
        </StyledTabPanel>
      </StyledTabList>
    </form>
  );
};

export { EditRulesForm, FormData };
