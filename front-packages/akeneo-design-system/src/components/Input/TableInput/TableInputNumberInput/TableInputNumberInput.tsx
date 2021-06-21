import {TableInputRow} from '../TableInputRow/TableInputRow';
import styled, {css} from 'styled-components';
import {NumberInput} from '../../NumberInput/NumberInput';
import {AkeneoThemedProps, getColor} from '../../../../theme';

const TableInputNumberInput = styled(NumberInput)<{highlighted: boolean} & AkeneoThemedProps>`
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

TableInputRow.displayName = 'TableInput.NumberInput';

export {TableInputNumberInput};
