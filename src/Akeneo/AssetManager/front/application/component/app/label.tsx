import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from 'akeneo-design-system';

const Label = styled.span<{readOnly?: boolean; isCode?: boolean} & AkeneoThemedProps>`
  color: ${({readOnly}) => getColor('grey', readOnly ? 100 : 120)};
  font-size: ${getFontSize('default')};
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;

  ::first-letter {
    text-transform: ${({isCode}) => (true === isCode ? 'initial' : 'capitalize')};
  }
`;

export {Label};
