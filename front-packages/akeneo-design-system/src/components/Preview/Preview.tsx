import React, {ReactNode, HTMLAttributes} from 'react';
import styled from 'styled-components';
import {getColor, getFontSize} from '../../theme';
import {Override} from '../../shared';

const PreviewContainer = styled.div`
  padding: 10px;
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

Preview.Highlight = Highlight;

export {Preview};
