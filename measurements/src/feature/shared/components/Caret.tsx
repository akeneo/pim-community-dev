import React from 'react';
import styled from 'styled-components';
import {Direction} from 'akeneomeasure/model/direction';

const CaretContainer = styled.span`
  display: inline-block;
  width: 0;
  height: 0;
  border-right: 4px solid transparent;
  border-left: 4px solid transparent;
  content: '';
  margin-bottom: 2px;
  margin-left: 5px;
`;

const DescendingCaret = styled(CaretContainer)`
  border-top: 4px solid ${props => props.theme.color.grey120};
`;

const AscendingCaret = styled(CaretContainer)`
  border-bottom: 4px solid ${props => props.theme.color.grey120};
`;

type CaretProps = {
  direction: Direction;
};

const Caret = ({direction}: CaretProps) =>
  direction === Direction.Ascending ? <AscendingCaret /> : <DescendingCaret />;

export {Caret};
