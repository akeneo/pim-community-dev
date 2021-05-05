import React, {Ref, ReactNode, isValidElement} from 'react';
import styled from 'styled-components';

//TODO be sure to select the appropriate container element here
const Tag = styled.div<{color: 'green' | 'blue' | 'dark blue' | 'purple' | 'dark purple' | 'yellow' | 'red'}>`
  border: 1px solid;
  border-color: ${({color}) =>
    ({
      green: '#81cccc',
      blue: '#4ca8e0',
      'dark blue': '#5e63b6',
      purple: '#9452ba',
      'dark purple': '#52267d',
      yellow: '#fcce76',
      red: '#f74b64',
    }[color])};
  color: ${({color}) =>
    ({
      green: '#5da8a6',
      blue: '#3278b7',
      'dark blue': '#3b438c',
      purple: '#763e9e',
      'dark purple': '#36145e',
      yellow: '#ca8411',
      red: '#c92343',
    }[color])};
  background-color: ${({color}) =>
    ({
      green: '#f5fafa',
      blue: '##f0f7fc',
      'dark blue': '#efeff8',
      purple: '#f3eef9',
      'dark purple': '#eeeaf2',
      yellow: '#fefbf2',
      red: '#fdedf0',
    }[color])};
  height: 18px;
  line-height: 16px;
  padding: 0 6px;
  display: inline;
  border-radius: 2px;
  font-size: 11px;
  margin: 0 6px;
`;
const TagsContainer = styled.div<{level: string}>``;

type TagsProps = {
  /**
   * TODO.
   */
  level?: 'primary' | 'warning' | 'danger';

  /**
   * TODO.
   */
  children?: ReactNode;
};

/**
 * TODO.
 */
const Tags = React.forwardRef<HTMLDivElement, TagsProps>(
  ({level = 'primary', children, ...rest}: TagsProps, forwardedRef: Ref<HTMLDivElement>) => {
    return (
      <TagsContainer level={level} ref={forwardedRef} {...rest}>
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
