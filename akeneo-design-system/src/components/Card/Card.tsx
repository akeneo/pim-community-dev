import React, {Ref, ReactNode, isValidElement, ReactElement} from 'react';
import styled from 'styled-components';
import {Badge, BadgeProps, Checkbox} from '../../components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';

const CardContainer = styled.div<CardProps & AkeneoThemedProps>`
  position: relative;
  display: flex;
  flex-direction: column;
  width: 140px;
  line-height: 16px;
  background-color: ${getColor('white')};
  font-size: ${getFontSize('default')};
  color: ${getColor('grey120')};
  cursor: ${({onClick}) => (undefined !== onClick ? 'pointer' : 'default')};

  img {
    box-sizing: border-box;
    border-style: solid;
    border-width: ${({isSelected}) => (isSelected ? '2px' : '1px')};
    border-color: ${({isSelected}) => (isSelected ? getColor('blue100') : getColor('grey100'))};
    width: 140px;
    height: 140px;
    margin-bottom: 7px;
  }
`;

const CardLabel = styled.div`
  display: flex;
  align-items: center;

  > :first-child {
    margin-right: 6px;
  }
`;

const BadgeContainer = styled.div`
  position: absolute;
  top: 10px;
  right: 10px;
`;

type CardProps = {
  /**
   * Source URL of the image to display in the Card.
   */
  src: string;

  /**
   * Whether or not the Card is selected.
   */
  isSelected?: boolean;

  /**
   * Handler called when the Card is selected. When provided, the Card will display a Checkbox and become selectable.
   */
  onSelectCard?: (isSelected: boolean) => void;

  /**
   * Children of the Card, contains the text to display below the image and can also contain a Badge component.
   */
  children: ReactNode;
};

/**
 * Cards are used to have a good visual representation of the items to display.
 * Cards can be used in a grid or in a collection.
 */
const Card = React.forwardRef<HTMLSpanElement, CardProps>(
  ({src, isSelected = false, onSelectCard, children, ...rest}: CardProps, forwardedRef: Ref<HTMLSpanElement>) => {
    const badges: ReactElement<BadgeProps>[] = [];
    const texts: string[] = [];
    React.Children.forEach(children, child => {
      if (isValidElement<BadgeProps>(child) && child.type === Badge) {
        badges.push(child);
      } else if (typeof child === 'string') {
        texts.push(child);
      }
    });

    const handleClick = undefined !== onSelectCard ? () => onSelectCard(!isSelected) : undefined;

    return (
      <CardContainer ref={forwardedRef} isSelected={isSelected} onClick={handleClick} {...rest}>
        {0 < badges.length && <BadgeContainer>{badges[0]}</BadgeContainer>}
        <img src={src} alt={texts[0]} />
        <CardLabel>
          {undefined !== onSelectCard && <Checkbox checked={isSelected ? 'true' : 'false'} />}
          {texts}
        </CardLabel>
      </CardContainer>
    );
  }
);

export {Card};
