import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor} from 'akeneo-design-system';

type Props = AkeneoThemedProps & {
  width?: number;
  isDraggable?: boolean;
  isActive?: boolean;
  rowTitle?: boolean;
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

const rowTitleStyle = css`
  font-style: italic;
  color: ${getColor('brand', 100)};
`;

const TableCell = styled.td.attrs((props: Props) => ({
  draggable: (props.isDraggable && props.isActive) || false,
}))<Props>`
  color: ${getColor('grey', 140)};
  width: ${props => (props.width !== undefined ? props.width : 'auto')};
  padding-left: 10px;

  ${props => props.isDraggable && props.isActive === true && draggableStyle}
  ${props => props.isDraggable && props.isActive === false && dragDisabledStyle}
  ${props => props.isActive === false && inactiveStyle}
  ${props => props.rowTitle && rowTitleStyle}
`;

export {TableCell};
