import {createContext} from 'react';

type SecurityContextValue = {
  isGranted: (acl: string) => boolean;
};

const SecurityContext = createContext<SecurityContextValue>({isGranted: () => true});

export {SecurityContextValue, SecurityContext};
