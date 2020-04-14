import React from "react";
import styled from "styled-components";
import { StyledLabel } from "./InputText";

const DivLabelWithFLag = styled.div`
  align-items: center;
  display: flex;
`;

const SpanSrOnly = styled.span`
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

type Props = {
  locale: string;
  label: string;
  flagDescription: string;
} & React.HTMLAttributes<HTMLLabelElement>;

const FlagLabel: React.FC<Props> = ({ locale, label, flagDescription }) => {
  const extractFlagFromLocale = (locale: string): string => {
    const region = locale.split('_')[locale.split('_').length - 1];

    return region.toLowerCase();
  };

  return (
    <DivLabelWithFLag>
      <StyledLabel
        className="AknFieldContainer-label control-label"
        htmlFor="label-input"
      >
        {label}
      </StyledLabel>
      <i className={`flag flag-${extractFlagFromLocale(locale)}`}>
        <SpanSrOnly>{flagDescription}</SpanSrOnly>
      </i>
    </DivLabelWithFLag>
  );
};

export { FlagLabel };
