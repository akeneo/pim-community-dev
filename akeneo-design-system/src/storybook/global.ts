import styled from 'styled-components';
import {CommonStyle} from '../theme';

const StoryStyle = styled.div`
  ${CommonStyle}
  & > * {
    margin: 0 10px 10px 0;
  }
`;

export {StoryStyle};
