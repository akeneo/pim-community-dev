import React, {Ref, ReactNode, isValidElement} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getFontSize} from '../../theme';

/**
 * The colors defined in this file are the alternative ones
 * You will find the hex colors on this page:
 * https://www.notion.so/akeneo/Alternative-colors-0f5283c1b02f4fd4a418f1e20f2efa99
 * Those colors will most likely only be used with the tags components
 */
type Tint = 'green' | 'blue' | 'dark_blue' | 'purple' | 'dark_purple' | 'yellow' | 'red';
type TagProps = {
  tint: Tint;
} & React.HTMLAttributes<HTMLLIElement>;
const Tag = styled.li<TagProps & AkeneoThemedProps>`
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
  font-size: ${getFontSize('small')};
  text-transform: uppercase;
  overflow: hidden;
  max-width: 200px;
  white-space: nowrap;
  text-overflow: ellipsis;
`;
const TagsContainer = styled.ul`
  display: flex;
  flex-wrap: wrap;
  padding-inline-start: 0;
  margin-block-end: 0;
  margin-block-start: 0;
  list-style-type: none;
  gap: 10px;
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
const Tags: React.FC<TagsProps> = React.forwardRef<HTMLUListElement, TagsProps>(
  ({children, ...rest}: TagsProps, forwardedRef: Ref<HTMLUListElement>) => {
    const getTitle = (children?: ReactNode) => {
      let label = '';

      React.Children.map(children, child => {
        if (typeof child === 'string') {
          label += child;
        }
      });

      return label;
    };

    return (
      <TagsContainer ref={forwardedRef} {...rest}>
        {React.Children.map(children, child => {
          if (isValidElement<TagProps>(child) && child.type === Tag) {
            //const tag = child as ReactElement<{title?: string; children?: ReactNode}, 'Tag'>;
            return React.cloneElement(child, {
              title: child.props?.title || getTitle(child.props?.children),
            });
          }
          throw new Error('A Tags element can only have Tag children');
        })}
      </TagsContainer>
    );
  }
);

export {Tags, Tag};
