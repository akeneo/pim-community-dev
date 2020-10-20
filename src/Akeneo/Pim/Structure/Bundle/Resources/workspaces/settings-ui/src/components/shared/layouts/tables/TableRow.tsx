import styled, {css} from 'styled-components';

type Props = {
  isSelected?: boolean;
  isDragged?: boolean;
};

const draggedStyle = css`
  opacity: 0.2;
`;

const selectionStyle = css`
  background-color: ${({theme}) => theme.color.blue20};
`;

const TableRow = styled.tr<Props>`
  cursor: pointer;
  height: 54px;
  border-bottom: 1px solid ${({theme}) => theme.color.grey60};

  :hover {
    background-color: ${({theme}) => theme.color.blue20};
  }

  ${props => props.isSelected && selectionStyle}

  ${props => props.isDragged && draggedStyle}
`;

export {TableRow};
