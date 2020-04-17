import React from "react";
import styled from "styled-components";
import { Header } from "../../template/Header";
import { PrimaryButton } from "../Buttons/PrimaryButton";
import { PimView } from "../../dependenciesTools/components/PimView";
import { Breadcrumb } from "../Breadcrumb";

const BreadcrumbAndButtons = styled.div`
  display: inline-flex;
  justify-content: space-between;
  width: 100%;
`;

type Props = {
  buttonLabel: string;
  formId: string;
  title: string;
};

const RulesHeader: React.FC<Props> = ({
  buttonLabel,
  formId,
  title,
  children
}) => {
  return (
    <Header>
      <BreadcrumbAndButtons>
        <Breadcrumb>{children}</Breadcrumb>
        <div className="AknTitleContainer-buttonsContainer">
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-rule-index-user-navigation"
          />
          <div className="AknTitleContainer-actionsContainer AknButtonList">
            <PrimaryButton form={formId} type="submit">
              {buttonLabel}
            </PrimaryButton>
          </div>
        </div>
      </BreadcrumbAndButtons>
      <div className="AknTitleContainer-line">
        <div className="AknTitleContainer-title">{title}</div>
      </div>
    </Header>
  );
};

RulesHeader.displayName = "RulesHeader";

export { RulesHeader };
