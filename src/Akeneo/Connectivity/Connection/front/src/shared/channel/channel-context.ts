import {createContext} from 'react';
import {Channel} from './use-channel';

export const ChannelContext = createContext<Channel[] | undefined>(undefined);
