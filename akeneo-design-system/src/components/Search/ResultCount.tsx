import React, {ReactNode, Ref} from 'react';
import styled from 'styled-components';
import {getColor} from '../../theme';
import {Override} from '../../shared';

const ResultCountContainer = styled.div`
  white-space: nowrap;
  color: ${getColor('purple', 100)};
  margin-left: 10px;
  line-height: 16px;
  text-transform: none;
`;

type ResultCountProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    children: ReactNode;
  }
>;

const ResultCount = React.forwardRef<HTMLDivElement, ResultCountProps>(
  ({children}: ResultCountProps, forwardedRef: Ref<HTMLDivElement>): React.ReactElement => {
    return <ResultCountContainer ref={forwardedRef}>{children}</ResultCountContainer>;
  }
);

export {ResultCount};
export type {ResultCountProps};
