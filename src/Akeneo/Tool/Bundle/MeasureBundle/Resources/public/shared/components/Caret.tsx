import styled from 'styled-components';
import React from 'react';

export enum Direction {
  Ascending = 'Ascending',
  Descending = 'Descending',
}

const DescendingCaret = styled.span`
  display: inline-block;
  width: 0;
  height: 0;
  border-top: 4px solid ${props => props.theme.color.grey120};
  border-right: 4px solid transparent;
  border-left: 4px solid transparent;
  content: '';
  margin-bottom: 2px;
`;

const AscendingCaret = styled.span`
  display: inline-block;
  width: 0;
  height: 0;
  border-bottom: 4px solid ${props => props.theme.color.grey120};
  border-right: 4px solid transparent;
  border-left: 4px solid transparent;
  content: '';
  margin-bottom: 2px;
`;

export const Caret = ({direction, onChange}: {direction: Direction; onChange: (newDirection: Direction) => void}) =>
  direction === Direction.Ascending ? (
    <AscendingCaret onClick={() => onChange(Direction.Descending)} />
  ) : (
    <DescendingCaret onClick={() => onChange(Direction.Ascending)} />
  );
