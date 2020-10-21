import React, {ReactElement, ReactNode, useContext} from 'react';
import {css} from 'styled-components';

const LoadingContext = React.createContext(false);

const Loading = ({loading, children}: {loading: boolean; children: ReactNode}): ReactElement => {
  const parentLoading = useContext(LoadingContext);

  return <LoadingContext.Provider value={loading || parentLoading}>{children}</LoadingContext.Provider>;
};

const placeholderStyle = css`
  @keyframes loading-breath {
    0% {
      background-position: 0% 50%;
    }
    50% {
      background-position: 100% 50%;
    }
    100% {
      background-position: 0% 50%;
    }
  }

  animation: loading-breath 2s infinite;
  background: linear-gradient(270deg, #fdfdfd, #eee);
  background-size: 400% 400%;
  border: none;
`;

export {Loading, LoadingContext, placeholderStyle};
