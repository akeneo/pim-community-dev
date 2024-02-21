import {AkeneoThemedProps, getColor} from '../../../../theme';
import {css} from 'styled-components';

const highlightCell = css<{highlighted?: boolean; inError?: boolean} & AkeneoThemedProps>`
  ${({highlighted, inError}) =>
    highlighted &&
    !inError &&
    css`
      background: ${getColor('green', 10)};
      box-shadow: 0 0 0 1px ${getColor('green', 80)};
    `};

  ${({inError}) =>
    inError &&
    css`
      background: ${getColor('red', 10)};
      box-shadow: 0 0 0 1px ${getColor('red', 80)};
    `};
`;

export {highlightCell};
