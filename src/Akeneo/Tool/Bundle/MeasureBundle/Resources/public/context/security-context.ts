import {createContext} from 'react';

type SecurityContextValue = (acl: string) => boolean;

const SecurityContext = createContext<SecurityContextValue>(() => true);

export {SecurityContextValue, SecurityContext};
