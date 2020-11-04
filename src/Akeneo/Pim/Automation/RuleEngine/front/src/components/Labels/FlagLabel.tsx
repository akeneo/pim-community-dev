import React from 'react';
import styled from 'styled-components';
import {Flag} from '../Flag/Flag';
import {Label} from './Label';

const DivLabelWithFLag = styled.div`
  align-items: center;
  display: flex;
`;

type Props = {
  locale: string;
  label: string;
  flagDescription: string;
} & React.AllHTMLAttributes<HTMLLabelElement>;

const FlagLabel: React.FC<Props> = ({
  flagDescription,
  htmlFor,
  label,
  locale,
}) => {
  return (
    <DivLabelWithFLag>
      <Label
        className='AknFieldContainer-label control-label'
        htmlFor={htmlFor}
        label={label}
      />
      <Flag locale={locale} flagDescription={flagDescription} />
    </DivLabelWithFLag>
  );
};

export {FlagLabel};
