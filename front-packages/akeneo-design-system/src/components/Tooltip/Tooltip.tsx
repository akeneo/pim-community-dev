import React, {HTMLAttributes, ReactNode, useRef, useEffect, RefObject, useState} from 'react';
import {createPortal} from 'react-dom';
import styled from 'styled-components';
import {Override} from '../../shared';
import {HelpPlainIcon} from '../../icons';
import {AkeneoThemedProps, CommonStyle, getColor, getFontSize} from '../../theme';
import {useBooleanState} from '../../hooks';

type Direction = 'top' | 'right' | 'bottom' | 'left';

const TooltipIconMargin = 5;
const TooltipContainer = styled.div<{size: number}>`
  position: relative;
  height: ${({size}) => size + TooltipIconMargin * 2}px;
  width: ${({size}) => size + TooltipIconMargin * 2}px;
  display: inline-block;
`;

const TooltipIcon = styled(HelpPlainIcon)`
  margin: ${TooltipIconMargin}px;
  color: ${getColor('blue', 100)};
`;

const TooltipContent = styled.div<{direction: Direction; width: number; top: number; left: number} & AkeneoThemedProps>`
  ${CommonStyle}
  position: fixed;
  z-index: 1901;
  border-radius: 4px;
  padding: 10px;
  width: ${({width}) => width}px;
  color: ${getColor('grey', 120)};
  background: ${getColor('blue', 10)};
  border: 1px solid ${getColor('blue', 40)};
  font-size: ${getFontSize('default')};
  line-height: 1;
  text-transform: none;
  box-shadow: 0 0 16px rgba(89, 146, 199, 0.25);
  top: ${({top}) => top}px;
  left: ${({left}) => left}px;
  opacity: ${({top, left}) => (-1 === top && -1 === left ? 0 : 1)};
`;

const computePosition = (
  direction: Direction,
  parentRef?: RefObject<HTMLDivElement>,
  elementRef?: RefObject<HTMLDivElement>
): number[] => {
  if (
    undefined === parentRef ||
    undefined === elementRef ||
    null === parentRef.current ||
    null === elementRef.current
  ) {
    return [-1, -1];
  }

  const {
    top: parentTop,
    left: parentLeft,
    width: parentWidth,
    height: parentHeight,
  } = parentRef.current.getBoundingClientRect();

  const {width: elementWidth, height: elementHeight} = elementRef.current.getBoundingClientRect();

  const relativeCenterTop = parentTop + parentHeight / 2 - elementHeight / 2;
  const relativeCenterLeft = parentLeft + parentWidth / 2 - elementWidth / 2;

  switch (direction) {
    default:
    case 'top':
      return [parentTop - elementHeight, relativeCenterLeft];
    case 'right':
      return [relativeCenterTop, parentLeft + parentWidth];
    case 'bottom':
      return [parentTop + parentHeight, relativeCenterLeft];
    case 'left':
      return [relativeCenterTop, parentLeft - elementWidth];
  }
};

export type TooltipProps = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Define the direction in which the tooltip will be rendered.
     */
    direction?: Direction;

    /**
     * Define the icon size.
     */
    iconSize?: number;

    /**
     * Content of the tooltip.
     */
    children: ReactNode;

    /**
     * Define the width of the tooltip.
     */
    width?: number;
  }
>;

const Tooltip: React.FC<TooltipProps> = ({direction = 'top', iconSize = 24, width = 200, children, ...rest}) => {
  const [isVisible, showTooltip, hideTooltip] = useBooleanState(false);
  const portalNode = document.createElement('div');
  portalNode.setAttribute('id', 'tooltip-root');
  const portalRef = useRef<HTMLDivElement>(portalNode);
  const parentRef = useRef<HTMLDivElement>(null);
  const contentRef = useRef<HTMLDivElement>(null);
  const [position, setPosition] = useState<number[]>([0, 0]);

  useEffect(() => {
    document.body.appendChild(portalRef.current);

    return () => {
      document.body.removeChild(portalRef.current);
    };
  }, []);

  useEffect(() => {
    setPosition(computePosition(direction, parentRef, contentRef));
  }, [children, direction, parentRef, contentRef, isVisible]);

  const [top, left] = position;

  return (
    <TooltipContainer
      ref={parentRef}
      role="tooltip"
      {...rest}
      size={iconSize}
      onMouseEnter={showTooltip}
      onMouseLeave={hideTooltip}
    >
      <TooltipIcon size={iconSize} />
      {isVisible &&
        createPortal(
          <TooltipContent ref={contentRef} direction={direction} width={width} top={top} left={left}>
            {children}
          </TooltipContent>,
          portalRef.current
        )}
    </TooltipContainer>
  );
};

export {Tooltip};
