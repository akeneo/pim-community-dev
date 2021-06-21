import {TableInputRow} from '../TableInputRow/TableInputRow';
import styled, {css} from 'styled-components';
import {TextInput} from '../../TextInput/TextInput';
import {AkeneoThemedProps, getColor} from '../../../../theme';

const TableInputTextInput = styled(TextInput)<{highlighted: boolean} & AkeneoThemedProps>`
  height: 39px;
  padding-left: 10px;
  padding-right: 10px;
  border-radius: 0;
  ${({highlighted}) =>
    highlighted
      ? css`
          background: ${getColor('green', 10)};
          border-color: ${getColor('green', 80)};
        `
      : css`
          background: none;
          border-color: rgba(0, 0, 0, 0);
        `};
`;

TableInputRow.displayName = 'TableInput.TextInput';

export {TableInputTextInput};
