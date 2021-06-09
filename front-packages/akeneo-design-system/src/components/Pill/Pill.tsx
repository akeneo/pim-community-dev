import React, {Ref} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColorForLevel} from '../../theme';

const PillContainer = styled.div<{level: PillLevel} & AkeneoThemedProps>`
  width: 10px;
  height: 10px;
  background-color: ${props => getColorForLevel(props.level, 100)};
  border-radius: 50%;
`;

type PillLevel = 'primary' | 'warning' | 'danger';

type PillProps = {
  /**
   * The level of the Pill.
   */
  level?: PillLevel;
};

const Pill = React.forwardRef<HTMLDivElement, PillProps>(
  ({level = 'warning', ...rest}: PillProps, forwardedRef: Ref<HTMLDivElement>) => {
    return <PillContainer role={level === 'danger' ? 'alert' : undefined} level={level} ref={forwardedRef} {...rest} />;
  }
);

export {Pill};
