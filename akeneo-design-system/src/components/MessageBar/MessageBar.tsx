import React, {ReactNode, ReactElement} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {CloseIcon, IconProps} from '../../icons';

type MessageBarLevel = 'info' | 'success' | 'warning' | 'danger';

const IconContainer = styled.div`
  padding: 0 25px;
`;

const Content = styled.div`
  padding: 10px 20px;
  font-size: ${getFontSize('small')};
  border-left: 1px solid;
  flex: 1;

  a {
    color: ${getColor('grey', 140)};
  }
`;

const Title = styled.div`
  font-size: ${getFontSize('bigger')};
  margin-bottom: 4px;
`;

//TODO TransparentButton in the DSM?
const CloseButton = styled.button`
  color: ${getColor('grey', 100)};
  padding: 0;
  border: 0;
  background: none;
  cursor: pointer;
  display: inline-flex;
`;

const Container = styled.div<{level: MessageBarLevel} & AkeneoThemedProps>`
  display: flex;
  align-items: center;
  min-width: 400px;
  max-width: 600px;
  padding: 10px 20px 10px 0;
  box-shadow: 2px 4px 8px 0 rgba(9, 30, 66, 0.25);
  ${Title}, ${IconContainer} {
    color: ${({level}) => getLevelColor(level)};
  }
  ${Content} {
    border-color: ${({level}) => getLevelColor(level)};
  }
`;

const getLevelColor = (level: MessageBarLevel) => {
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

type MessageBarProps = {
  /**
   * Defines the level of the MessageBar, changing the color accent
   */
  level?: MessageBarLevel;

  /**
   * The title to display
   */
  title: string;

  /**
   * Icon to display
   */
  icon: ReactElement<IconProps>;

  /**
   * Content of the MessageBar
   */
  children?: ReactNode;
};

/**
 * A message bar is a message that communicates information to the user.
 */
const MessageBar = ({level = 'info', title, icon, children}: MessageBarProps) => {
  return (
    <Container level={level}>
      <IconContainer>{React.cloneElement(icon, {size: 24})}</IconContainer>
      <Content>
        <Title>{title}</Title>
        {children}
      </Content>
      <CloseButton>
        <CloseIcon size={24} />
      </CloseButton>
    </Container>
  );
};

export {MessageBar};
