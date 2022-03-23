import React, {ReactNode, HTMLAttributes, cloneElement, isValidElement, ReactElement} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';
import {IconButton, IconButtonProps} from '../IconButton/IconButton';

const PreviewContainer = styled.div`
  padding: 10px 15px;
  background: ${getColor('blue', 10)};
  border-radius: 3px;
  border: 1px solid ${getColor('blue', 40)};
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

const PreviewTitle = styled.div`
  text-transform: uppercase;
  font-size: ${getFontSize('small')};
  color: ${getColor('blue', 100)};
`;

const PreviewList = styled.div`
  overflow-wrap: break-word;
  white-space: break-spaces;
  color: ${getColor('grey', 140)};
`;

const Highlight = styled.span`
  color: ${getColor('brand', 100)};
  font-weight: bold;
`;

const ActionsContainer = styled.div`
  display: none;
  align-items: center;
  height: 0;

  button:hover:not([disabled]) {
    background: none;
  }
`;

const RowContainer = styled.div`
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin: 0 -4px;
  padding: 4px;

  &:hover {
    background: ${getColor('blue', 20)};

    ${ActionsContainer} {
      display: flex;
    }
  }
`;

type RowProps = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Add an action that will be displayed on the right of the Preview Row.
     */
    action?: ReactElement<IconButtonProps>;

    /**
     * Content of the Preview Row.
     */
    children?: ReactNode;
  }
>;

const Row = ({action, children}: RowProps) => {
  return (
    <RowContainer>
      {children}
      {action && (
        <ActionsContainer>
          {isValidElement<IconButtonProps>(action) && action.type === IconButton
            ? cloneElement(action, {
                level: 'tertiary',
                ghost: 'borderless',
                size: 'small',
              })
            : action}
        </ActionsContainer>
      )}
    </RowContainer>
  );
};

type PreviewProps = Override<
  HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Title of the preview.
     */
    title: string;

    /**
     * Content of the preview.
     */
    children?: ReactNode;
  }
>;

/**
 * Preview component is used to put emphasis on some content.
 */
const Preview = ({title, children, ...rest}: PreviewProps) => {
  return (
    <PreviewContainer {...rest}>
      <PreviewTitle>{title}</PreviewTitle>
      <PreviewList>{children}</PreviewList>
    </PreviewContainer>
  );
};

Highlight.displayName = 'Preview.Highlight';
Row.displayName = 'Preview.Row';

Preview.Highlight = Highlight;
Preview.Row = Row;

export {Preview};
