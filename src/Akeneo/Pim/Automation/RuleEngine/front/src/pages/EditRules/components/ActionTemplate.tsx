import React from "react";
import styled from "styled-components";
import { Translate } from "../../../dependenciesTools";
import { RedGhostButton, SmallHelper } from "../../../components";
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
  title: string;
  helper: string;
  srOnly: string;
};

const ActionTemplate: React.FC<Props> = ({
  srOnly,
  translate,
  title,
  helper,
  children,
}) => {
  return (
    <fieldset>
      <StyledHeader className="AknSubsection-title">
        <StyledHeaderLeft>
          <TextBoxBlue>
            {translate("pimee_catalog_rule.rule.action.then.label")}
          </TextBoxBlue>
          <StyledTitleHeader>{title}</StyledTitleHeader>
        </StyledHeaderLeft>
        <RedGhostButton sizeMode="small">
          {translate("pimee_catalog_rule.form.edit.remove_action")}
        </RedGhostButton>
      </StyledHeader>
      <SmallHelper>{helper}</SmallHelper>
      <LegendSrOnly>{srOnly}</LegendSrOnly>
      {children}
    </fieldset>
  );
};

ActionTemplate.displayName = "ActionTemplate";

export { ActionTemplate };
