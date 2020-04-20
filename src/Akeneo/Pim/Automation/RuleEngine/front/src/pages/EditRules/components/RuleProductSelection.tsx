import React from "react";
import styled from "styled-components";
import { Translate } from "../../../dependenciesTools";
import { GreyGhostButton, SmallHelper } from "../../../components";
import { TextBoxBlue } from "./TextBoxBlue";
import { RuleDefinition } from "../../../models/RuleDefinition";
import { PimConditionLine } from "../PimConditionLine";
import { FallbackConditionLine } from "../FallbackConditionLine";
import { Condition } from "../../../models/Condition";
import { Locale } from "../../../models/Locale";

const Header = styled.header`
  font-weight: normal;
  margin-bottom: 0;
  width: 100%;
`;

const LegendSrOnly = styled.legend`
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
`;

const HeaderPartContainer = styled.span`
  display: flex;
  align-items: center;
`;

const TitleHeader = styled.span`
  padding-left: 8px;
`;

const AddConditionContainer = styled.div`
  border-left: 1px solid ${({ theme }) => theme.color.grey100};
  display: flex;
  margin-left: 15px;
  padding-left: 15px;
`;

type Props = {
  register: any,
  ruleDefinition: RuleDefinition,
  translate: Translate;
  activatedLocales: Locale[]
};

type ConditionLineProps = {
  register: any;
  condition: Condition;
  lineNumber: number,
  translate: Translate;
  activatedLocales: Locale[];
};

const ConditionLine: React.FC<ConditionLineProps> = ({
    register,
    translate,
    condition,
    lineNumber,
    activatedLocales
  }) => {
  const Line = condition.module;
  const isFallback =
    condition.module === PimConditionLine ||
    condition.module === FallbackConditionLine;

  return (
    <div
      className={`AknGrid-bodyRow${isFallback &&
        " AknGrid-bodyRow--highlight"}`}
    >
      <div className="AknGrid-bodyCell">
        <Line
          register={register}
          condition={condition}
          lineNumber={lineNumber}
          translate={translate}
          activatedLocales={activatedLocales}
        />
      </div>
    </div>
  );
};

const RuleProductSelection: React.FC<Props> = ({ register, ruleDefinition, translate, activatedLocales }) => {
  return (
    <fieldset>
      <Header className="AknSubsection-title">
        <HeaderPartContainer>
          <TextBoxBlue>
            {translate("pimee_catalog_rule.rule.condition.if.label")}
          </TextBoxBlue>
          <TitleHeader>
            {translate("pimee_catalog_rule.form.edit.product_selection")}
          </TitleHeader>
        </HeaderPartContainer>
        <HeaderPartContainer>
          <span className="AknSubsection-comment AknSubsection-comment--clickable">
            {translate("pimee_catalog_rule.form.edit.count_products", {
              count: "0",
            })}
          </span>
          <AddConditionContainer>
            <GreyGhostButton sizeMode="small">
              {translate("pimee_catalog_rule.form.edit.add_conditions")}
            </GreyGhostButton>
          </AddConditionContainer>
        </HeaderPartContainer>
      </Header>
      <SmallHelper>
        {translate("pimee_catalog_rule.form.helper.product_selection")}
        <a href="#">
          {translate(
            "pimee_catalog_rule.form.helper.product_selection_doc_link"
          )}
        </a>
      </SmallHelper>
      <div className="AknGrid AknGrid--unclickable">
        {ruleDefinition.conditions.map((condition, i) => {
          return (
            <ConditionLine
              register={register}
              condition={condition}
              lineNumber={i}
              translate={translate}
              key={`condition_${i}`}
              activatedLocales={activatedLocales}
            />
          );
        })}
      </div>
      <LegendSrOnly>
        {translate("pimee_catalog_rule.form.legend.product_selection")}
      </LegendSrOnly>
    </fieldset>
  );
};

RuleProductSelection.displayName = "RuleProductSelection";

export { RuleProductSelection };
