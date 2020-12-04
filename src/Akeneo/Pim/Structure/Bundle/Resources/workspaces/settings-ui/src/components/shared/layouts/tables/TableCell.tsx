import styled, {css} from 'styled-components';

type Props = {
  width?: number;
  isDraggable?: boolean;
  isActive?: boolean;
};

const draggableStyle = css`
  cursor: move;
`;

const dragDisabledStyle = css`
  cursor: default;
`;

const inactiveStyle = css`
  opacity: 0.4;
`;

const TableCell = styled.td.attrs((props: Props) => ({
  draggable: (props.isDraggable && props.isActive) || false,
}))<Props>`
  color: ${({theme}) => theme.color.purple100};
  width: ${props => (props.width !== undefined ? props.width : 'auto')};
  font-weight: bold;
  padding-left: 10px;

  ${props => props.isDraggable && props.isActive === true && draggableStyle}

  ${props => props.isDraggable && props.isActive === false && dragDisabledStyle}

  ${props => props.isActive === false && inactiveStyle}
`;

export {TableCell};
