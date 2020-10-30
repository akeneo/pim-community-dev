import React, {FC, ReactNode} from 'react';
import styled from 'styled-components';

type Props = {
  type: ReactNode;
};

const Container = styled.span`
  padding: 0 20px;
  color: ${props => props.theme.color.grey100}
`;

const Icon: FC<Props> = ({children}) => {
  return <Container>{children}</Container>;
}

export {Icon};
