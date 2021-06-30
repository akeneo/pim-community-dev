import {TableInputRow} from '../TableInputRow/TableInputRow';
import styled, {css} from 'styled-components';
import {TextInput} from '../../TextInput/TextInput';
import {AkeneoThemedProps, getColor} from '../../../../theme';

const TableInputText = styled(TextInput)<{highlighted: boolean} & AkeneoThemedProps>`
  height: 39px;
  padding-left: 10px;
  padding-right: 10px;
  border-radius: 0;
  border: none;

  ${({highlighted}) =>
    highlighted
      ? css`
          background: ${getColor('green', 10)};
          box-shadow: 0 0 0 1px ${getColor('green', 80)};
        `
      : css`
          background: none;
        `};

  &:focus {
    box-shadow: 0 0 0 1px ${getColor('grey', 100)};
  }
`;

TableInputRow.displayName = 'TableInput.TextInput';

export {TableInputText};
