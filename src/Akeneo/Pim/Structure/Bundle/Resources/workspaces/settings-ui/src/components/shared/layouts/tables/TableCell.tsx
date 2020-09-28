import styled, {css} from 'styled-components';

type Props = {
  width?: number;
  isDraggable?: boolean;
};

const draggableStyle = css`
  cursor: move;
`;

const TableCell = styled.td.attrs((props: Props) => ({
  draggable: props.isDraggable || false,
}))<Props>`
  color: ${({theme}) => theme.color.purple100};
  width: ${props => (props.width !== undefined ? props.width : 'auto')};
  font-style: italic;
  font-weight: bold;
  padding-left: 20px;

  ${props => props.isDraggable && draggableStyle}
`;

export {TableCell};
