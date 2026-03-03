import React, {Children, cloneElement, HTMLAttributes, isValidElement, ReactNode} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Button, ButtonProps, IconButton, IconButtonProps} from '../../components';
import {Override} from '../../shared';

const SectionTitleContainer = styled.div<{sticky?: number} & AkeneoThemedProps>`
  display: flex;
  width: 100%;
  gap: 10px;
  align-items: center;
  height: 44px;
  line-height: 44px;
  border-bottom: 1px solid ${getColor('grey', 140)};

  ${({sticky}) =>
    undefined !== sticky &&
    css`
      position: sticky;
      top: ${sticky}px;
      background-color: ${getColor('white')};
      z-index: 9;
    `}
`;

const TitleContainer = styled.h2<TitleProps & AkeneoThemedProps>`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
  font-weight: 400;
  text-transform: ${({level}) => ('primary' === level ? 'uppercase' : 'unset')};
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
`;

type TitleProps = Override<
  HTMLAttributes<HTMLHeadingElement>,
  {
    level?: 'primary' | 'secondary';
  }
>;

const Title = ({level = 'primary', ...rest}: TitleProps) => (
  <TitleContainer as={'secondary' === level ? 'h3' : 'h2'} level={level} {...rest} />
);

const Spacer = styled.div`
  flex-grow: 1;
`;

const Separator = styled.div`
  border-left: 1px solid ${getColor('grey', 100)};
  margin: 0 10px;
  height: 24px;
`;

const Information = styled.div`
  font-size: ${getFontSize('default')};
  font-weight: normal;
  color: ${getColor('brand', 100)};
  white-space: nowrap;
`;

type SectionTitleProps = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    /**
     * When set, defines the sticky top position of the Section Title.
     */
    sticky?: number;

    /**
     * The content of the section title.
     */
    children?: ReactNode;
  }
>;

/**
 * The section title allows users to correctly identify the content of the section.
 */
const SectionTitle = ({children, ...rest}: SectionTitleProps) => {
  const decoratedChildren = Children.map(children, child => {
    if (isValidElement<IconButtonProps>(child) && child.type === IconButton) {
      return cloneElement(child, {
        level: 'tertiary',
        size: 'small',
        ghost: 'borderless',
      });
    }

    if (isValidElement<ButtonProps>(child) && child.type === Button) {
      return cloneElement(child, {
        level: 'tertiary',
        size: 'small',
        ghost: true,
        ...child.props,
      });
    }

    return child;
  });

  return <SectionTitleContainer {...rest}>{decoratedChildren}</SectionTitleContainer>;
};

SectionTitle.Title = Title;
SectionTitle.Spacer = Spacer;
SectionTitle.Separator = Separator;
SectionTitle.Information = Information;

export {SectionTitle};
