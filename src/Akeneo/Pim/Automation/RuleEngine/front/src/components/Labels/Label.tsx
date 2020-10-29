import React from 'react';
import {VisuallyHidden} from 'reakit/VisuallyHidden';
import styled from 'styled-components';

const StyledLabel = styled.label`
  padding-bottom: 5px;
`;

type Props = {
  label: string;
  hiddenLabel?: boolean;
} & React.LabelHTMLAttributes<HTMLLabelElement>;

const Label: React.FC<Props> = ({htmlFor, label, hiddenLabel}) => {
  const HTMLLabel = (
    <StyledLabel
      className='AknFieldContainer-label control-label'
      htmlFor={htmlFor}>
      {label}
    </StyledLabel>
  );
  return hiddenLabel ? <VisuallyHidden>{HTMLLabel}</VisuallyHidden> : HTMLLabel;
};

export {Label};
