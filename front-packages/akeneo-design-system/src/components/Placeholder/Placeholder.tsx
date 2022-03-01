import React, {cloneElement, FC, HTMLAttributes, ReactElement} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {IllustrationProps} from '../../illustrations/IllustrationProps';

type PlaceholderSize = 'default' | 'large';

const CenteredHelperContainer = styled.div<{size: PlaceholderSize}>`
  padding: 0 20px;
  display: flex;
  align-items: center;
  flex-direction: column;
  gap: ${({size}) => ('large' === size ? 20 : 5)}px;
`;

const CenteredHelperTitle = styled.div<{size: PlaceholderSize} & AkeneoThemedProps>`
  color: ${getColor('grey', 140)};
  font-size: ${({size}) => getFontSize('large' === size ? 'title' : 'big')};
  line-height: ${({size}) => getFontSize('large' === size ? 'title' : 'big')};
  text-align: center;
`;

type PlaceholderProps = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    title: string;
    illustration: ReactElement<IllustrationProps>;
    size?: PlaceholderSize;
  }
>;

const Placeholder: FC<PlaceholderProps> = ({illustration, title, size = 'default', children, ...rest}) => {
  return (
    <CenteredHelperContainer size={size} {...rest}>
      {cloneElement(illustration, {size: 'large' === size ? 256 : 120})}
      <CenteredHelperTitle size={size}>{title}</CenteredHelperTitle>
      {children}
    </CenteredHelperContainer>
  );
};

export {Placeholder};
