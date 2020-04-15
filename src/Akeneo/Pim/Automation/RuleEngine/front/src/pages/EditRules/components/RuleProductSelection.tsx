import React from "react";
import styled from "styled-components";
import { Translate } from "../../../dependenciesTools";
import { SmallHelper } from "../../../components/SmallHelper";
import { GreyGhostButton } from "../../../components/Buttons";
import { TextBoxBlue } from "./TextBoxBlue";
import {RuleDefinition} from "../../../models/RuleDefinition";

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
  ruleDefinition: RuleDefinition
  translate: Translate;
};

const RuleProductSelection: React.FC<Props> = ({ ruleDefinition, translate }) => {
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
              count: "0"
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
      {ruleDefinition.conditions.map((condition, i) => {
        const Line = condition.module;
        return <Line
          condition={condition}
          translate={translate}
          key={`condition_${i}`}
        />
      })}
      <LegendSrOnly>
        {translate("pimee_catalog_rule.form.legend.product_selection")}
      </LegendSrOnly>
    </fieldset>
  );
};

RuleProductSelection.displayName = "RuleProductSelection";

export { RuleProductSelection };
