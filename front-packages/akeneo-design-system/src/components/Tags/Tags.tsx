import React, {Ref, ReactNode, isValidElement} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColorAlternative, getFontSize} from '../../theme';

/**
 * The colors defined in this file are the alternative ones
 * You will find the hex colors on this page:
 * https://www.notion.so/akeneo/Alternative-colors-0f5283c1b02f4fd4a418f1e20f2efa99
 * Those colors will most likely only be used with the tags components
 */
type Tint =
  | 'green'
  | 'blue'
  | 'dark_blue'
  | 'purple'
  | 'dark_purple'
  | 'yellow'
  | 'red'
  | 'dark_cyan'
  | 'forest_green'
  | 'olive_green'
  | 'hot_pink'
  | 'coral_red'
  | 'orange'
  | 'chocolate';

// Because tints are in snake_case, and colors code are in camelCase
const convertTintToColorCode = (str: string) => {
  return str.replace(/_([a-z])/g, function (g) {
    return g[1].toUpperCase();
  });
};

type TagProps = {
  tint: Tint;
} & React.HTMLAttributes<HTMLLIElement>;
const Tag = styled.li<TagProps & AkeneoThemedProps>`
  border: 1px solid;
  border-color: ${({tint}) => getColorAlternative(convertTintToColorCode(tint), 100)};
  color: ${({tint}) => getColorAlternative(convertTintToColorCode(tint), 120)};
  background-color: ${({tint}) => getColorAlternative(convertTintToColorCode(tint), 10)};
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
const Tags = React.forwardRef<HTMLUListElement, TagsProps>(
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
