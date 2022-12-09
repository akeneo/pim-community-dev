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

const TooltipTitle = styled.div`
  font-weight: 600;
  margin-bottom: 5px;
`;

const TooltipContent = styled.div<{direction: string} & AkeneoThemedProps>`
  position: absolute;
  border-radius: 4px;
  left: 50%;
  padding: 10px;
  color: ${getColor('blue', 120)};
  background: ${getColor('blue', 10)};
  border: 1px solid ${getColor('blue', 40)};
  font-size: ${getFontSize('default')};
  line-height: 1;
  z-index: 100;
  white-space: nowrap;
  box-shadow: 0px 0px 16px rgba(89, 146, 199, 0.25);

  ${({direction}) => {
    switch (direction) {
      case 'top':
        return `
                bottom: calc(100% + ${TooltipMargin});
                transform: translateX(-50%);
              `;
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
    direction?: 'top' | 'right' | 'bottom' | 'left';
    title?: string;
    content: string;
    iconSize?: number;
  }
>;

const Tooltip = forwardRef<HTMLDivElement, TooltipProps>(
  ({direction = 'top', title, content, iconSize = 24, ...rest}: TooltipProps, forwardedRef: Ref<HTMLDivElement>) => {
    const [visible, setVisible] = useState(false);
    const showTooltip = () => setVisible(true);
    const hideTooltip = () => setVisible(false);
    const theme = useTheme();

    return (
      <TooltipContainer ref={forwardedRef} {...rest} onMouseEnter={showTooltip} onMouseLeave={hideTooltip}>
        <HelpPlainIcon size={iconSize} color={theme.color.blue100} />
        {visible && (
          <TooltipContent direction={direction}>
            {title && <TooltipTitle>{title}</TooltipTitle>}
            {content}
          </TooltipContent>
        )}
      </TooltipContainer>
    );
  }
);

export {Tooltip};
