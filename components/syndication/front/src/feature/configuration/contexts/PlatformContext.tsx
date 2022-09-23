import React, {createContext, useContext, ReactNode} from 'react';

type PlatformValue = 'walmart' | 'amazon';

const PlatformContext = createContext<PlatformValue>('walmart');

const usePlatform = (): PlatformValue => {
  return useContext(PlatformContext);
};

type PlatformProviderProps = {
  platform: PlatformValue;
  children: ReactNode;
};

const PlatformProvider = ({platform, children}: PlatformProviderProps) => {
  return <PlatformContext.Provider value={platform}>{children}</PlatformContext.Provider>;
};

export {PlatformProvider, usePlatform};

export type {PlatformValue};
