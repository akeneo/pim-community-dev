import React from 'react';
import styled from 'styled-components';
import {Override} from '../../../../shared';
import {AkeneoThemedProps, getColor} from '../../../../theme';
import {Tag} from '../../../Tags/Tags';

const Container = styled.a<{active: boolean; disabled: boolean} & AkeneoThemedProps>`
  box-sizing: border-box;
  cursor: ${({disabled}) => (disabled ? 'not-allowed' : 'pointer')};
  color: ${({active, disabled}) =>
    disabled ? getColor('grey', 100) : active ? getColor('brand', 100) : getColor('grey', 140)};
  display: flex;
  height: 38px;
  margin: 0;
  outline: none;
  padding: 10px 30px;
  text-decoration: none;
  :hover {
    color: ${({disabled}) => !disabled && getColor('brand', 100)};
  }
  :focus:not(:active) {
    box-shadow: 0 0 0 2px ${getColor('blue', 40)};
  }
`;

const TagContainer = styled.div`
  margin-left: 10px;
`;

type Props = Override<
  React.HTMLAttributes<HTMLAnchorElement>,
  {
    /**
     * Children are a string label
     */
    children?: React.ReactNode;

    /**
     * Define if the component is active
     */
    active?: boolean;

    /**
     * Define if the component will be displayed as disabled
     */
    disabled?: boolean;

    /**
     * Url to go to if the button is clicked
     */
    href?: string;
  }
>;

const Item = React.forwardRef<HTMLAnchorElement, Props>(
  ({children, href, disabled, active, onClick, ...rest}, forwardedRef: React.Ref<HTMLAnchorElement>) => {
    const handleClick = (event: React.MouseEvent<HTMLAnchorElement>) => {
      if (disabled) {
        event.preventDefault();

        return;
      }

      onClick?.(event);
    };

    let tag: React.ReactElement | null = null;
    const content = React.Children.map(children, child => {
      if (React.isValidElement(child) && child.type === Tag) {
        if (null === tag) {
          tag = child;

          return null;
        }
        throw new Error('You can only provide one component of type Tag.');
      }

      return child;
    });

    return (
      <Container
        ref={forwardedRef}
        href={disabled ? undefined : href}
        active={active}
        disabled={disabled}
        aria-disabled={disabled}
        onClick={handleClick}
        {...rest}
      >
        {content}
        {tag && <TagContainer>{tag}</TagContainer>}
      </Container>
    );
  }
);

export {Item};
