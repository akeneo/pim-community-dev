import React, {ReactNode, isValidElement, ReactElement} from 'react';
import styled from 'styled-components';
import {Checkbox} from '../../components';
import {AkeneoThemedProps, getColor, getFontSize, placeholderStyle} from '../../theme';
import {Override} from '../../shared';

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
  cursor: ${({onClick, disabled}) => (disabled ? 'not-allowed' : undefined !== onClick ? 'pointer' : 'auto')};

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

const BadgeContainer = styled.div`
  position: absolute;
  z-index: 5;
  top: 10px;
  right: 10px;
`;

type CardProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Source URL of the image to display in the Card.
     */
    src: string | null;

    /**
     * Should the image cover all the Card container or be contained in it.
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
  ...rest
}: CardProps) => {
  const nonLabelChildren: ReactElement[] = [];
  const texts: string[] = [];

  React.Children.forEach(children, child => {
    if (typeof child === 'string') {
      texts.push(child);
    } else if (isValidElement(child)) {
      nonLabelChildren.push(child);
    }
  });

  const toggleSelect = undefined !== onSelect && !disabled ? () => onSelect(!isSelected) : undefined;

  return (
    <CardContainer fit={fit} isSelected={isSelected} onClick={onClick || toggleSelect} disabled={disabled} {...rest}>
      <ImageContainer isLoading={null === src}>
        <Overlay />
        <img src={src ?? ''} alt={texts[0]} />
      </ImageContainer>
      <CardLabel>
        {undefined !== onSelect && (
          <Checkbox aria-label={texts[0]} checked={isSelected} readOnly={disabled} onChange={toggleSelect} />
        )}
        <CardText title={texts[0]}>{texts}</CardText>
      </CardLabel>
      {nonLabelChildren}
    </CardContainer>
  );
};

Card.BadgeContainer = BadgeContainer;

export {Card, CardGrid};
