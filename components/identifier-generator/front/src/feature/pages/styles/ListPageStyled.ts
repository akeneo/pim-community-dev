import styled from 'styled-components';
import {getColor} from 'akeneo-design-system';

const HelpCenterLink = styled.a`
  font-size: ${({theme}) => theme.fontSize.big};
  color: ${({theme}) => theme.color.purple100};
  margin-top: 5px;
`;

const Label = styled.label`
  font-style: italic;
  color: ${getColor('brand', 100)};
  cursor: pointer;
`;

const Styled = {
  HelpCenterLink,
  Label,
};

export {Styled};
