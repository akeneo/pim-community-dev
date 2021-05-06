import React, {Ref, ReactNode, isValidElement} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps} from 'theme';

//TODO be sure to select the appropriate container element here
const Tag = styled.div<{tint: 'green' | 'blue' | 'dark_blue' | 'purple' | 'dark_purple' | 'yellow' | 'red'} & AkeneoThemedProps>`
  border: 1px solid;
  border-color: ${({tint}) =>
    ({
      green: '#81cccc',
      blue: '#4ca8e0',
      dark_blue: '#5e63b6',
      purple: '#9452ba',
      dark_purple: '#52267d',
      yellow: '#fcce76',
      red: '#f74b64',
    }[tint])};
  color: ${({tint}) =>
    ({
      green: '#5da8a6',
      blue: '#3278b7',
      dark_blue: '#3b438c',
      purple: '#763e9e',
      dark_purple: '#36145e',
      yellow: '#ca8411',
      red: '#c92343',
    }[tint])};
  background-color: ${({tint}) =>
    ({
      green: '#f5fafa',
      blue: '#f0f7fc',
      dark_blue: '#efeff8',
      purple: '#f3eef9',
      dark_purple: '#eeeaf2',
      yellow: '#fefbf2',
      red: '#fdedf0',
    }[tint])};
  height: 16px;
  line-height: 16px;
  padding: 0 6px;
  display: inline-block;
  border-radius: 2px;
  font-size: 11px;
  margin: 0 10px 10px 0;
  text-transform:uppercase;
  overflow:hidden;
  max-width:200px;
  white-space:nowrap;
  text-overflow:ellipsis;
`;
const TagsContainer = styled.div`
    margin-right:-10px;
    margin-bottom:-10px;
`;

type TagsProps = {
  /**
   * list of Tag elements.
   */
  children?: ReactNode;
};

/**
 * This component displays a set of Tag elements inline.
 */
const Tags = React.forwardRef<HTMLDivElement, TagsProps>(
  ({children, ...rest}: TagsProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <TagsContainer  ref={forwardedRef} {...rest}>
        {React.Children.map(children, child => {
          if (isValidElement(child) && child.type === Tag) {
            return child;
          }
          throw new Error('A Tags element can only have Tag children');
        })}
      </TagsContainer>
    );
  }
);

export {Tags, Tag};
