import React from "react";
import styled from "styled-components";
import { Translate } from "../../../dependenciesTools";
import { SmallHelper } from "../../../components/SmallHelper";
import { RedGhostButton } from "../../../components/Buttons";
import { TextBoxBlue } from "./TextBoxBlue";

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

const StyledHeader = styled.header`
  font-weight: normal;
  margin-bottom: 0;
  width: 100%;
`;

const StyledHeaderLeft = styled.span`
  align-items: center;
  display: flex;
`;

const StyledTitleHeader = styled.span`
  padding-left: 8px;
`;

type Props = {
  translate: Translate;
};

const RuleAddToCategory: React.FC<Props> = ({ translate }) => {
  return (
    <fieldset>
      <StyledHeader className="AknSubsection-title">
        <StyledHeaderLeft>
          <TextBoxBlue>
            {translate("pimee_catalog_rule.rule.action.then.label")}
          </TextBoxBlue>
          <StyledTitleHeader>
            {translate("pimee_catalog_rule.form.edit.add_to_category")}
          </StyledTitleHeader>
        </StyledHeaderLeft>
        <RedGhostButton sizeMode="small">
          {translate("pimee_catalog_rule.form.edit.remove_action")}
        </RedGhostButton>
      </StyledHeader>
      <SmallHelper>
        {translate("pimee_catalog_rule.form.helper.add_to_category")}
      </SmallHelper>
      <LegendSrOnly>{translate("pimee_catalog_rule.form.legend.add_to_category")}</LegendSrOnly>
    </fieldset>
  );
};

RuleAddToCategory.displayName = "RuleAddToCategory";

export { RuleAddToCategory };
