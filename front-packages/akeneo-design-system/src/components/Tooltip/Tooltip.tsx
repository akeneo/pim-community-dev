import React, {forwardRef, Ref, HTMLAttributes, useState} from 'react';
import styled from 'styled-components';
import {Override} from '../../shared';
import {HelpPlainIcon} from '../../icons';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {useTheme} from '../../hooks';

const TooltipMargin = '5px';

const TooltipContainer = styled.div`
  position: relative;
  display: inline-block;
`;

const TooltipContent = styled.div<{direction: string; zIndex: number} & AkeneoThemedProps>`
  position: absolute;
  border-radius: 4px;
  left: 50%;
  padding: 10px;
  color: ${getColor('blue', 120)};
  background: ${getColor('blue', 10)};
  border: 1px solid ${getColor('blue', 40)};
  font-size: ${getFontSize('default')};
  line-height: 1;
  z-index: ${({zIndex}) => zIndex};
  white-space: nowrap;
  box-shadow: 0px 0px 16px rgba(89, 146, 199, 0.25);

  ${({direction}) => {
    switch (direction) {
      case 'bottom':
        return `
                top: calc(100% + ${TooltipMargin});
                transform: translateX(-50%);
              `;
      case 'left':
        return `
                left: auto;
                top: 50%;
                right: calc(100% + ${TooltipMargin});
                transform: translateY(-50%);
              `;
      case 'right':
        return `
                top: 50%;
                left: calc(100% + ${TooltipMargin});
                transform: translateY(-50%);
              `;
      default:
        return `
                bottom: calc(100% + ${TooltipMargin});
                transform: translateX(-50%);
              `;
    }
  }}
`;

type TooltipProps = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Define the direction in which the tooltip will be rendered
     */
    direction?: 'top' | 'right' | 'bottom' | 'left';
    /**
     * Define the position order of the tooltip
     */
    zIndex?: number;
    /**
     * Define the icon size
     */
    iconSize?: number;
    /**
     * Content of the tooltip
     */
    children: React.ReactNode;
  }
>;

const Tooltip = forwardRef<HTMLDivElement, TooltipProps>(
  (
    {direction = 'top', zIndex = 100, iconSize = 24, children, ...rest}: TooltipProps,
    forwardedRef: Ref<HTMLDivElement>
  ) => {
    const [visible, setVisible] = useState(false);
    const showTooltip = () => setVisible(true);
    const hideTooltip = () => setVisible(false);
    const theme = useTheme();

    return (
      <TooltipContainer ref={forwardedRef} {...rest} onMouseEnter={showTooltip} onMouseLeave={hideTooltip}>
        <HelpPlainIcon size={iconSize} color={theme.color.blue100} />
        {visible && (
          <TooltipContent direction={direction} zIndex={zIndex}>
            {children}
          </TooltipContent>
        )}
      </TooltipContainer>
    );
  }
);

export {Tooltip};
