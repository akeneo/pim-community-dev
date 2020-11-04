import styled from 'styled-components';

const NodeLineContainer = styled.span`
  display: flex;
  align-items: center;
  height: 40px;
  opacity: ${(props: {opacity?: number}) => props.opacity || 1};
`;

export {NodeLineContainer};
