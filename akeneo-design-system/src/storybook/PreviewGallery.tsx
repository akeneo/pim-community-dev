import styled from 'styled-components';
import {AkeneoThemedProps, CommonStyle, getColor} from '../theme';

const StoryStyle = styled.div`
  ${CommonStyle}
  & > * {
    margin: 0 10px 20px 0;
  }
`;

const PreviewGrid = styled.div<{width: number}>`
  display: grid;
  grid-template-columns: repeat(auto-fill, ${({width}) => width}px);
  gap: 16px;
  margin-bottom: 50px;
`;

PreviewGrid.defaultProps = {
  width: 140,
};

const PreviewCard = styled.div`
  display: flex;
  flex-direction: column;
  height: 100%;
  text-align: center;
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: rgba(0, 0, 0, 0.1) 0 1px 3px 0;
  border-radius: 4px;
`;

const PreviewContainer = styled.div`
  padding: 20px;
  color: ${getColor('grey100')};
  overflow: hidden;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
`;

const LabelContainer = styled.div`
  padding: 8px 0px;
  max-width: 100%;
  white-space: nowrap;
  word-break: break-word;
  overflow: hidden;
  text-overflow: ellipsis;
`;

const Subtitle = styled.h2`
  text-transform: Capitalize;
`;

const Content = styled.div<{width: number; height: number} & AkeneoThemedProps>`
  width: ${({width}) => width}px;
  height: ${({height}) => height}px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid ${getColor('blue', 40)};
  background-color: ${getColor('blue', 10)};
  margin-top: 30px;
`;

const MessageBarContainer = styled.div`
  padding: 5px;
  width: 600px;
  height: 110px;
  overflow: clip;
`;

const Scrollable = styled.div<{height: number}>`
  overflow: auto;
  height: ${({height}) => height}px;
`;

export {
  StoryStyle,
  PreviewGrid,
  PreviewCard,
  PreviewContainer,
  LabelContainer,
  Subtitle,
  Content,
  MessageBarContainer,
  Scrollable,
};
