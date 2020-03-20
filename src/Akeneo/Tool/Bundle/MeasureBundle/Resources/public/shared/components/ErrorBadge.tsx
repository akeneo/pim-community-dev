import styled from 'styled-components';

const ErrorBadge = styled.div`
  width: 10px;
  height: 10px;
  background-color: ${props => props.theme.color.red100};
  border-radius: 50%;
`;

export {ErrorBadge};
