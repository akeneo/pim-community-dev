import styled from 'styled-components';

const Link = styled.a`
  font-weight: 400;
  text-decoration: underline;
  color: ${props => props.theme.color.purple100};
  cursor: pointer;
`;

export {Link};
