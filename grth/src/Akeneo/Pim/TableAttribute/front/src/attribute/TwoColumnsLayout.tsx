import React from 'react';
import styled from 'styled-components';

const TwoColumnsLayoutContainer = styled.div`
  display: grid;
  margin-top: 20px;
  grid-template-columns: 1fr 400px;
  grid-template-rows: 1fr;
  gap: 0 40px;
  grid-template-areas: '. .';
`;

type TwoColumnsLayoutProps = {
  rightColumn: React.ReactElement;
};

const TwoColumnsLayout: React.FC<TwoColumnsLayoutProps> = ({rightColumn, children, ...rest}) => {
  return (
    <TwoColumnsLayoutContainer {...rest}>
      {children}
      <div>{rightColumn}</div>
    </TwoColumnsLayoutContainer>
  );
};

export {TwoColumnsLayout};
