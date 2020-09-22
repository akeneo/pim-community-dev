import React, {FC} from 'react';
import styled, {css} from 'styled-components';

type Props = {
  onClick?: () => void;
  isSelected?: boolean;
};

const Row = styled.tr<Props>`
  cursor: pointer;
  height: 54px;
  border-bottom: 1px solid ${({theme}) => theme.color.grey70};

  :hover {
    background-color: ${({theme}) => theme.color.grey60};
  }

  ${({theme, isSelected}) =>
    isSelected &&
    css`
      background-color: ${theme.color.blue20};
    `}
`;

const TableRow: FC<Props> = ({children, ...props}) => {
  return <Row {...props}>{children}</Row>;
};

export {TableRow};
