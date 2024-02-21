import {useContext} from 'react';
import {NotifyContext} from './notify-context';

export const useNotify = () => useContext(NotifyContext);
