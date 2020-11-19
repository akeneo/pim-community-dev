import React, {Children, FC, isValidElement, ReactNode} from 'react';
import styled from 'styled-components';

type Props = {
  type: ReactNode;
};

const Container = styled.span`
  height: 20px;
  padding: 0 20px;
  color: ${props => props.theme.color.grey100};
`;

const Icon: FC<Props> = ({children}) => {
  return (
    <Container>
      {Children.map(children, child => {
        if (!isValidElement(child)) {
          return child;
        }

        return React.cloneElement(child, {
          width: 20,
          height: 20,
        });
      })}
    </Container>
  );
};

export {Icon};
