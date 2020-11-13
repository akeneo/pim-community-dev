import React, {ReactNode, ReactElement, isValidElement, useEffect, useState, useCallback} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {CloseIcon, IconProps} from '../../icons';
import {LinkProps, Link} from '../../components';

type MessageBarLevel = 'info' | 'success' | 'warning' | 'danger';

const IconContainer = styled.div`
  padding: 0 25px;
`;

const Progress = styled.svg.attrs(({ratio}: {ratio: number; level: MessageBarLevel}) => ({
  style: {strokeDashoffset: `calc(100% * ${Math.PI * ratio - Math.PI})`},
}))<{ratio: number; level: MessageBarLevel} & AkeneoThemedProps>`
  position: absolute;
  overflow: visible;
  top: -10%;
  left: -10%;
  width: 120%;
  height: 120%;

  circle {
    fill: transparent;
    stroke: ${({level}) => getLevelColor(level)};
    stroke-linecap: round;
    stroke-width: 5%;
    stroke-dasharray: calc(100% * ${Math.PI});
    transform: rotate(-88deg);
    transform-origin: 50% 50%;
    transition: all 1s linear;
  }
`;

const Content = styled.div`
  padding: 10px 20px;
  font-size: ${getFontSize('small')};
  border-left: 1px solid;
  flex: 1;
  line-height: 1.5;

  a {
    color: ${getColor('grey', 140)};
  }
`;

const Title = styled.div`
  font-size: ${getFontSize('bigger')};
  margin-bottom: 4px;
`;

const Timer = styled.div`
  font-weight: 100;
`;

const Icon = styled(CloseIcon)``;

//TODO TransparentButton in the DSM?
const CloseButton = styled.button<{autoHide: boolean} & AkeneoThemedProps>`
  position: relative;
  width: 24px;
  height: 24px;
  color: ${getColor('grey', 100)};
  padding: 0;
  border: 0;
  background: none;
  cursor: pointer;
  display: inline-flex;
  font-size: ${getFontSize('bigger')};
  overflow: visible;

  & > * {
    position: absolute;
    line-height: 24px;
    width: 100%;
    top: 0;
    left: 0;
    transition: opacity 0.2s ease-in-out;
  }

  ${Icon} {
    opacity: ${({autoHide}) => (autoHide ? 0 : 1)};
  }
  ${Timer} {
    opacity: ${({autoHide}) => (autoHide ? 1 : 0)};
  }

  :hover {
    ${Icon} {
      opacity: 1;
    }

    ${Timer} {
      opacity: 0;
    }
  }
`;

const ANIMATION_DURATION = 1000;
const AnimateContainer = styled.div<{mounted: boolean}>`
  transition: transform ${ANIMATION_DURATION}ms ease-in-out;
  transform: translateX(${({mounted}) => (mounted ? 0 : 'calc(100% + 50px)')});
`;

const AnimateMessageBar = ({children}: {children: ReactElement<MessageBarProps>}) => {
  if (children.type !== MessageBar) {
    throw new Error('Only MessageBar element can be passed to AnimateMessageBar');
  }

  const [mounted, setMounted] = useState(false);
  useEffect(() => {
    setMounted(true);
  }, []);
  const onClose = useCallback(() => {
    setMounted(false);

    setTimeout(() => {
      children.props.onClose();
    }, ANIMATION_DURATION);
  }, []);

  return <AnimateContainer mounted={mounted}>{React.cloneElement(children, {onClose})}</AnimateContainer>;
};

const Container = styled.div<{level: MessageBarLevel} & AkeneoThemedProps>`
  display: flex;
  align-items: center;
  min-width: 400px;
  max-width: 500px;
  padding: 10px 20px 10px 0;
  box-shadow: 2px 4px 8px 0 rgba(9, 30, 66, 0.25);

  ${Title}, ${IconContainer} {
    color: ${({level}) => getLevelColor(level)};
  }

  ${Content} {
    border-color: ${({level}) => getLevelColor(level)};
  }
`;

const getLevelColor = (level: MessageBarLevel): ((props: AkeneoThemedProps) => string) => {
  switch (level) {
    case 'info':
      return getColor('blue', 100);
    case 'success':
      return getColor('green', 100);
    case 'warning':
      return getColor('yellow', 120);
    case 'danger':
      return getColor('red', 100);
  }
};

const getDuration = (level: MessageBarLevel): number => {
  switch (level) {
    case 'success':
      return 3;
    case 'info':
    case 'warning':
      return 5;
    case 'danger':
      return 8;
  }
};

const useOver = () => {
  const [over, setOver] = useState<boolean>(false);
  const onMouseOver = useCallback(() => {
    setOver(true);
  }, []);

  const onMouseOut = useCallback(() => {
    setOver(false);
  }, []);

  return [over, onMouseOver, onMouseOut] as const;
};

type MessageBarProps = {
  /**
   * Defines the level of the MessageBar, changing the color accent.
   */
  level?: MessageBarLevel;

  /**
   * The title to display.
   */
  title: string;

  /**
   * Icon to display.
   */
  icon: ReactElement<IconProps>;

  /**
   * Handler called when the MessageBar is closed.
   */
  onClose: () => void;

  /**
   * Content of the MessageBar.
   */
  children?: ReactNode;
};

/**
 * A message bar is a message that communicates information to the user.
 */
const MessageBar = ({level = 'info', title, icon, onClose, children}: MessageBarProps) => {
  const duration = getDuration(level);
  const autoHide = !React.Children.toArray(children).some(
    child => isValidElement<LinkProps>(child) && child.type === Link
  );

  const [remaining, setRemaining] = useState<number>(autoHide ? duration : 0);
  const [over, onMouseOver, onMouseOut] = useOver();

  useEffect(() => {
    if (!autoHide) return;

    const intervalId = setInterval(
      () =>
        setRemaining(remaining => {
          if (0 > remaining) {
            clearInterval(intervalId);
            onClose();

            return remaining;
          }

          return remaining - 1;
        }),
      1000
    );

    if (over) {
      clearInterval(intervalId);
      return;
    }

    return () => clearInterval(intervalId);
  }, [over]);

  useEffect(() => {
    setRemaining(remaining => remaining - 1);
  }, []);

  const countDownFinished = -1 === remaining;
  const remainingDisplay = countDownFinished ? '' : Math.min(remaining + 1, duration);

  return (
    <Container level={level} onMouseOver={onMouseOver} onMouseOut={onMouseOut}>
      <IconContainer>{React.cloneElement(icon, {size: 24})}</IconContainer>
      <Content>
        <Title>{title}</Title>
        {children}
      </Content>
      <CloseButton onClick={onClose} autoHide={autoHide && !countDownFinished}>
        <Timer>
          {remainingDisplay}
          {autoHide && (
            <Progress ratio={remaining / duration} level={level}>
              <circle r="50%" cx="50%" cy="50%" />
            </Progress>
          )}
        </Timer>
        <Icon size={24} />
      </CloseButton>
    </Container>
  );
};

export {MessageBar, AnimateMessageBar};
