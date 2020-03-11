import styled from 'styled-components';

export const Link = styled.a`
  font-weight: 400;
  text-decoration: underline;
  color: ${props => props.theme.color.purple100};
  cursor: pointer;
`;
