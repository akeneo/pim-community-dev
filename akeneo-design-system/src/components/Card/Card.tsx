import React, {isValidElement, ReactElement, ReactNode, useRef, MouseEvent} from 'react';
import styled, {css} from 'styled-components';
import {Checkbox, Link, LinkProps} from '../../components';
import {AkeneoThemedProps, getColor, getFontSize, placeholderStyle} from '../../theme';
import {Override} from '../../shared';

type StackProps = {
  isSelected: boolean;
};

const Stack = styled.div.attrs(() => ({
  role: 'none',
}))<StackProps & AkeneoThemedProps>`
  ::before,
  ::after {
    content: ' ';
    position: absolute;
    top: 0;
    left: 0;
    width: 95%;
    height: 95%;
    box-sizing: border-box;
    border-style: solid;
    border-width: ${({isSelected}) => (isSelected ? 2 : 1)}px;
    border-color: ${({isSelected}) => getColor(isSelected ? 'blue' : 'grey', 100)};
    background-color: ${getColor('white')};
  }

  ::before {
    transform: translate(6px, 6px);
  }

  ::after {
    transform: translate(3px, 3px);
  }
`;

type CardGridProps = {
  size?: 'normal' | 'big';
};

const CardGrid = styled.div<CardGridProps & AkeneoThemedProps>`
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(${({size}) => ('big' === size ? 200 : 140)}px, 1fr));
  gap: ${({size}) => ('big' === size ? 40 : 20)}px;

  ${({size}) =>
    'big' === size &&
    css`
      ${Stack} {
        ::before {
          transform: translate(8px, 10px);
        }

        ::after {
          transform: translate(4px, 5px);
        }
      }
    `}
`;

CardGrid.defaultProps = {
  size: 'normal',
};

const Overlay = styled.div<{stacked: boolean} & AkeneoThemedProps>`
  position: absolute;
  z-index: 2;
  top: 0;
  width: ${({stacked}) => (stacked ? '95%' : '100%')};
  height: ${({stacked}) => (stacked ? '95%' : '100%')};
  background-color: ${getColor('grey', 140)};
  opacity: 0%;
  transition: opacity 0.3s ease-in;
`;

const CardContainer = styled.div<{fit: string; disabled: boolean; actionable: boolean} & AkeneoThemedProps>`
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  line-height: 20px;
  font-size: ${getFontSize('default')};
  color: ${getColor('grey', 120)};
  cursor: ${({actionable, disabled}) => (disabled ? 'not-allowed' : actionable ? 'pointer' : 'auto')};

  img {
    position: absolute;
    top: 0;
    object-fit: ${({fit}) => fit};
    width: ${({stacked}) => (stacked ? '95%' : '100%')};
    height: ${({stacked}) => (stacked ? '95%' : '100%')};
    box-sizing: border-box;
    border-style: solid;
    border-width: ${({isSelected}) => (isSelected ? 2 : 1)}px;
    border-color: ${({isSelected}) => getColor(isSelected ? 'blue' : 'grey', 100)};
  }

  a,
  a:hover {
    color: inherit;
    text-decoration: none;
    background-color: ${getColor('white')};
  }
`;

const ImageContainer = styled.div<{isLoading: boolean}>`
  ${({isLoading}) => isLoading && placeholderStyle}

  position: relative;

  ::before {
    content: '';
    display: block;
    padding-bottom: 100%;
  }

  :hover ${Overlay} {
    opacity: 50%;
  }
`;

const CardLabel = styled.div`
  display: flex;
  align-items: center;
  margin-top: 7px;

  > :first-child {
    margin-right: 6px;
  }
`;

const CardText = styled.span`
  white-space: nowrap;
  text-overflow: ellipsis;
  overflow: hidden;
`;

const BadgeContainer = styled.div<{stacked: boolean} & AkeneoThemedProps>`
  position: absolute;
  z-index: 5;
  top: 10px;
  right: ${({stacked}) => (stacked ? '20px' : '10px')};
`;
BadgeContainer.displayName = 'BadgeContainer';
BadgeContainer.defaultProps = {
  stacked: false,
};

type CardProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Source URL of the image to display in the Card.
     */
    src: string | null;

    /**
     * Should the Card image cover all the Card container or be contained in it.
     */
    fit?: 'cover' | 'contain';

    /**
     * Whether or not the Card is selected.
     */
    isSelected?: boolean;

    /**
     * Wether or not the Card is selectable and clickable.
     */
    disabled?: boolean;

    /**
     * Handler called when the Card is selected. When provided, the Card will display a Checkbox and become selectable.
     */
    onSelect?: (isSelected: boolean) => void;

    /**
     * Add a visual representation of a collection for the same item
     */
    stacked?: boolean;

    /**
     * Children of the Card, contains the text to display below the image and can also contain a Badge component.
     */
    children: ReactNode;
  }
>;

/**
 * Cards are used to have a good visual representation of the items to display.
 * Cards can be used in a grid or in a collection.
 */
const Card = ({
  src,
  fit = 'cover',
  isSelected = false,
  onSelect,
  disabled = false,
  children,
  onClick,
  stacked = false,
  ...rest
}: CardProps) => {
  const linkRef = useRef<HTMLAnchorElement>(null);

  const nonLabelChildren: ReactElement[] = [];
  const links: ReactElement<LinkProps>[] = [];
  const texts: string[] = [];

  React.Children.forEach(children, (child, key) => {
    if (typeof child === 'string') {
      texts.push(child);
    } else if (isValidElement(child)) {
      if (Link === child.type) {
        links.push(React.cloneElement(child, {key, ref: linkRef, disabled}));
      } else {
        let props: {stacked?: boolean} & React.Attributes = {key: child.key};
        if (child.type === BadgeContainer) {
          props = {
            ...props,
            stacked,
          };
        }
        nonLabelChildren.push(React.cloneElement(child, props));
      }
    }
  });

  const handleClick = (event: MouseEvent<HTMLDivElement>) => {
    if (disabled || (null !== linkRef.current && linkRef.current === event.target)) {
      return;
    }

    if (null !== linkRef.current && linkRef.current !== event.target) {
      linkRef.current.click();
    } else if (undefined !== onClick) {
      onClick(event);
    } else {
      onSelect?.(!isSelected);
    }
  };

  return (
    <CardContainer
      fit={fit}
      isSelected={isSelected}
      actionable={0 < links.length || undefined !== onClick}
      onClick={handleClick}
      disabled={disabled}
      stacked={stacked}
      {...rest}
    >
      <ImageContainer isLoading={null === src}>
        {stacked && <Stack isSelected={isSelected} data-testid="stack" />}
        <Overlay stacked={stacked} />
        <img src={src ?? ''} alt={texts[0]} />
      </ImageContainer>
      <CardLabel>
        {undefined !== onSelect && (
          <Checkbox aria-label={texts[0]} checked={isSelected} readOnly={disabled} onChange={onSelect} />
        )}
        <CardText title={texts[0]}>
          {texts}
          {links}
        </CardText>
      </CardLabel>
      {nonLabelChildren}
    </CardContainer>
  );
};

Card.BadgeContainer = BadgeContainer;

export {Card, CardGrid};
