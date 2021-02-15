import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from '../../theme';
import {Button, ButtonProps, IconButton, IconButtonProps} from '..';
import {Override} from '../../shared';

const SectionTitleContainer = styled.div`
  width: 100%;
  display: flex;
  gap: 10px;
  align-items: center;
  height: 44px;
  line-height: 44px;
  border-bottom: 1px solid ${getColor('grey', 140)};
`;

const Title = styled.h2`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
  font-weight: 400;
  text-transform: uppercase;
  text-overflow: ellipsis;
  white-space: nowrap;
  overflow: hidden;
`;

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
`;

type SectionTitleProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * The content of the section title.
     */
    children?: ReactNode;
  }
>;

/**
 * It identify the function of the group
 */
const SectionTitle = ({children, ...rest}: SectionTitleProps) => {
  const decoratedChildren = React.Children.map(children, child => {
    if (React.isValidElement<IconButtonProps>(child) && child.type === IconButton) {
      return React.cloneElement(child, {
        level: 'tertiary',
        size: 'small',
        ghost: 'borderless',
      });
    }

    if (React.isValidElement<ButtonProps>(child) && child.type === Button) {
      return React.cloneElement(child, {
        level: 'tertiary',
        size: 'small',
        ghost: true,
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
