import React from "react";
import styled from "styled-components";
import { StyledLabel } from "./InputText";
import {Flag} from "../Flag/Flag";

const DivLabelWithFLag = styled.div`
  align-items: center;
  display: flex;
`;

type Props = {
  locale: string;
  label: string;
  flagDescription: string;
} & React.HTMLAttributes<HTMLLabelElement>;

const FlagLabel: React.FC<Props> = ({ locale, label, flagDescription }) => {
  return (
    <DivLabelWithFLag>
      <StyledLabel
        className="AknFieldContainer-label control-label"
        htmlFor="label-input"
      >
        {label}
      </StyledLabel>
      <Flag locale={locale} flagDescription={flagDescription}/>
    </DivLabelWithFLag>
  );
};

export { FlagLabel };
