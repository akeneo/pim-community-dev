import React, {ReactNode} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from '../../../theme';

const TableCellContainer = styled.td<{isHighlighted: boolean} & AkeneoThemedProps>`
  color: ${getColor('grey', 140)};
  border-bottom: 1px solid ${getColor('grey', 60)};
  padding: 15px 10px;
  ${props =>
    props.isHighlighted &&
    css`
      color: ${getColor('purple', 100)};
      font-style: italic;
      font-weight: bold;
      font-family: Lato;
    `}
`;
const TableCellInnerContainer = styled.div`
  display: flex;
`;

type TableCellProps = {
  /**
   * Define that cell information is important
   */
  isHighlighted?: boolean;

  /**
   * Content of the cell
   */
  children?: ReactNode;
};

const TableCell = ({isHighlighted = false, children, ...rest}: TableCellProps) => {
  return (
    <TableCellContainer isHighlighted={isHighlighted} {...rest}>
      <TableCellInnerContainer>{children}</TableCellInnerContainer>
    </TableCellContainer>
  );
};

export {TableCell};
