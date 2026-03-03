import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';

const ToolbarContainer = styled.div<{isVisible: boolean} & AkeneoThemedProps>`
  display: flex;
  gap: 15px;
  padding: 0 15px;
  background-color: ${getColor('white')};
  align-items: center;
  border-top: 1px solid ${({isVisible}) => (!isVisible ? 'transparent' : getColor('grey', 80))};
  flex-basis: ${({isVisible}) => (!isVisible ? 0 : '70px')};
  min-height: ${({isVisible}) => (!isVisible ? 0 : '70px')};
  transition: flex-basis 0.3s ease-in-out, min-height 0.3s ease-in-out, border 0.3s ease-in-out;
  overflow: ${({isVisible}) => (!isVisible ? 'hidden' : 'visible')};
`;

const SelectionContainer = styled.div`
  display: flex;
  gap: 10px;
  align-items: center;
`;

const ActionsContainer = styled.div`
  display: flex;
  gap: 10px;
  align-items: center;
`;

const LabelContainer = styled.div`
  white-space: nowrap;
  color: ${getColor('grey', 120)};
  text-transform: uppercase;
  font-size: ${getFontSize('default')};
  align-items: center;
`;

type ToolbarProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Define if the toolbar should be visible.
     */
    isVisible?: boolean;

    /**
     * The content of the Toolbar.
     */
    children?: ReactNode;
  }
>;

/**
 * The toolbar is useful to display actions on a selection.
 */
const Toolbar = ({isVisible = true, children, ...rest}: ToolbarProps) => {
  return (
    <ToolbarContainer isVisible={isVisible} {...rest}>
      {children}
    </ToolbarContainer>
  );
};

Toolbar.LabelContainer = LabelContainer;
Toolbar.SelectionContainer = SelectionContainer;
Toolbar.ActionsContainer = ActionsContainer;

export {Toolbar};
