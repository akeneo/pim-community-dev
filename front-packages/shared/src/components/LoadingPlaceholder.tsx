import styled, {keyframes} from 'styled-components';

const loadingBreath = keyframes`
    0%{background-position:0 50%}
    50%{background-position:100% 50%}
    100%{background-position:0 50%}
`;

const LoadingPlaceholderContainer = styled.div`
  > * {
    position: relative;
    border: none !important;
    border-radius: 5px;
    overflow: hidden;

    &:after {
      animation: ${loadingBreath} 2s infinite;
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;

      background: linear-gradient(270deg, #fdfdfd, #eee);
      background-size: 400% 400%;
      border-radius: 5px;
    }
  }
`;

export {LoadingPlaceholderContainer};
