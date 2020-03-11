import React from 'react';
import styled from 'styled-components';

export enum Direction {
  Ascending = 'Ascending',
  Descending = 'Descending',
}

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
  onChange?: (newDirection: Direction) => void;
};

export const Caret = ({direction, onChange}: CaretProps) =>
  direction === Direction.Ascending ? (
    <AscendingCaret onClick={() => onChange && onChange(Direction.Descending)} />
  ) : (
    <DescendingCaret onClick={() => onChange && onChange(Direction.Ascending)} />
  );
