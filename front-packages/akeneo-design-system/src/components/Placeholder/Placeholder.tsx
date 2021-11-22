import React, {ReactElement} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {IllustrationProps} from '../../illustrations/IllustrationProps';

const CenteredHelperContainer = styled.div`
  text-align: center;
  & > * {
    display: block;
    margin: auto;
  }
  padding: 0 20px;
`;

const CenteredHelperTitle = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
`;

type PlaceholderProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    title: string;
    illustration: ReactElement<IllustrationProps>;
  }
>;

const Placeholder: React.FC<PlaceholderProps> = ({illustration, title, children, ...rest}) => {
  return (
    <CenteredHelperContainer {...rest}>
      {React.cloneElement(illustration, {size: 120})}
      <CenteredHelperTitle>{title}</CenteredHelperTitle>
      {children}
    </CenteredHelperContainer>
  );
};

export {Placeholder};
