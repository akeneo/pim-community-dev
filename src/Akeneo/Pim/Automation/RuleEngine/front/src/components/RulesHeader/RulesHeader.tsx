import React from "react";
import styled from "styled-components";
import { Header } from "../Header";
import { PrimaryButton } from "../Buttons/PrimaryButton";
import { PimView } from "../../dependenciesTools/components/PimView";
import { Translate } from "../../dependenciesTools";
import { Breadcrumb } from "../Breadcrumb";
import { UnsavedChangesWarning } from "../UnsavedChangesWarning";

const BreadcrumbAndButtons = styled.div`
  display: inline-flex;
  justify-content: space-between;
  width: 100%;
`;

type Props = {
  buttonLabel: string;
  formId: string;
  title: string;
  translate: Translate;
  unsavedChanges?: boolean;
};

const RulesHeader: React.FC<Props> = ({
  buttonLabel,
  children,
  formId,
  title,
  translate,
  unsavedChanges = false,
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
              {translate(buttonLabel)}
            </PrimaryButton>
          </div>
        </div>
      </BreadcrumbAndButtons>
      <div className="AknTitleContainer-line">
        <div className="AknTitleContainer-title">{title}</div>
        {unsavedChanges && <UnsavedChangesWarning translate={translate} />}
      </div>
    </Header>
  );
};

RulesHeader.displayName = "RulesHeader";

export { RulesHeader };
