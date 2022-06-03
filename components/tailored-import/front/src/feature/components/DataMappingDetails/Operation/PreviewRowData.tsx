import React, {ReactElement, ReactNode} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, IconButtonProps, placeholderStyle, Preview} from 'akeneo-design-system';

const PreviewContent = styled.div<{isLoading: boolean; isEmpty: boolean; hasError: boolean} & AkeneoThemedProps>`
  overflow-wrap: anywhere;
  overflow: hidden;
  height: 18px;

  ${({isEmpty}) =>
    isEmpty &&
    css`
      color: ${getColor('grey', 100)};
    `}

  ${({isLoading}) => isLoading && placeholderStyle}
  ${({hasError}) =>
    hasError &&
    css`
      color: ${getColor('red', 100)};
    `}
`;

type PreviewRowDataProps = {
  action?: ReactElement<IconButtonProps>;
  children: ReactNode;
  hasError: boolean;
  isEmpty?: boolean;
  isLoading?: boolean;
};

const PreviewRowData = ({
  action,
  isLoading = false,
  isEmpty = false,
  hasError = false,
  children,
}: PreviewRowDataProps) => {
  return (
    <Preview.Row action={action}>
      <PreviewContent isLoading={isLoading} isEmpty={isEmpty} hasError={hasError}>
        {children}
      </PreviewContent>
    </Preview.Row>
  );
};

export {PreviewRowData};
