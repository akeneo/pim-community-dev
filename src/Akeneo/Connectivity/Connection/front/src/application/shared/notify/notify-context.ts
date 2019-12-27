import {createContext} from 'react';
import {Notify} from './notify.interface';

export const NotifyContext = createContext<Notify>(() => undefined);
