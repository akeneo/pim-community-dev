import React, {Ref, ReactNode, isValidElement, ReactElement} from 'react';
import styled, {css} from 'styled-components';
import {Badge, BadgeProps, Checkbox} from '../../components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {useSkeleton} from '../../hooks';
import {applySkeletonStyle, SkeletonProps} from '../Skeleton/Skeleton';

type CardGridProps = {
  size?: 'normal' | 'big';
};

const CardGrid = styled.div<CardGridProps>`
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(${({size}) => ('big' === size ? 200 : 140)}px, 1fr));
  gap: ${({size}) => ('big' === size ? 40 : 20)}px;
`;

CardGrid.defaultProps = {
  size: 'normal',
};

const Overlay = styled.div`
  position: absolute;
  z-index: 2;
  top: 0;
  width: 100%;
  padding-bottom: 100%;
  background-color: ${getColor('grey140')};
  opacity: 0%;
  transition: opacity 0.3s ease-in;
`;

const CardContainer = styled.div<CardProps & AkeneoThemedProps>`
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  line-height: 20px;
  font-size: ${getFontSize('default')};
  color: ${getColor('grey120')};
  cursor: ${({onClick}) => (undefined !== onClick ? 'pointer' : 'default')};

  img {
    position: absolute;
    top: 0;
    object-fit: ${({fit}) => fit};
    width: 100%;
    height: 100%;
    box-sizing: border-box;
    border-style: solid;
    border-width: ${({isSelected}) => (isSelected ? '2px' : '1px')};
    border-color: ${({isSelected}) => (isSelected ? getColor('blue100') : getColor('grey100'))};
  }
`;

const ImageContainer = styled.div<SkeletonProps>`
  position: relative;

  ::before {
    content: '';
    display: block;
    padding-bottom: 100%;
  }

  :hover ${Overlay} {
    opacity: 50%;
  }

  ${applySkeletonStyle(
    css`
      border-radius: 3px;
      & img {
        opacity: 0;
      }
    `
  )}
`;

const CardLabel = styled.div<SkeletonProps>`
  display: flex;
  align-items: center;
  margin-top: 7px;

  > :first-child {
    margin-right: 6px;
  }

  & > label {
    ${applySkeletonStyle()}
  }
`;

const BadgeContainer = styled.div`
  position: absolute;
  z-index: 5;
  top: 10px;
  right: 10px;
`;

const Text = styled.span<SkeletonProps>`
  ${applySkeletonStyle(
    css`
      border-radius: 3px;
    `
  )}
`;

type CardProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Source URL of the image to display in the Card.
     */
    src: string;

    /**
     * Should the image cover all the Card container or be contained in it.
     */
    fit?: 'cover' | 'contain';

    /**
     * Whether or not the Card is selected.
     */
    isSelected?: boolean;

    /**
     * Handler called when the Card is selected. When provided, the Card will display a Checkbox and become selectable.
     */
    onSelect?: (isSelected: boolean) => void;

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
const Card = React.forwardRef<HTMLDivElement, CardProps>(
  (
    {src, fit = 'cover', isSelected = false, onSelect, children, ...rest}: CardProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const badges: ReactElement<BadgeProps>[] = [];
    const texts: string[] = [];
    React.Children.forEach(children, child => {
      if (isValidElement<BadgeProps>(child) && child.type === Badge) {
        badges.push(child);
      } else if (typeof child === 'string') {
        texts.push(child);
      } else {
        throw new Error('Card component only accepts string or Badge as children');
      }
    });

    const toggleSelect = undefined !== onSelect ? () => onSelect(!isSelected) : undefined;
    const skeleton = useSkeleton();

    return (
      <CardContainer ref={forwardedRef} fit={fit} isSelected={isSelected} onClick={toggleSelect} {...rest}>
        {0 < badges.length && <BadgeContainer>{badges[0]}</BadgeContainer>}
        <ImageContainer skeleton={skeleton}>
          <Overlay />
          <img src={src} alt={texts[0]} />
        </ImageContainer>
        <CardLabel skeleton={skeleton}>
          {undefined !== onSelect ? (
            <Checkbox checked={isSelected} onChange={toggleSelect}>
              {texts}
            </Checkbox>
          ) : (
            <Text skeleton={skeleton}>{texts}</Text>
          )}
        </CardLabel>
      </CardContainer>
    );
  }
);

export {Card, CardGrid};
