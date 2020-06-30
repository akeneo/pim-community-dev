import {useContext} from 'react';
import {EditingOptionContextState} from './useEditingOptionContextState';
import {EditingOptionContext} from '../contexts';

export const useEditingOptionContext = (): EditingOptionContextState => {
    return useContext(EditingOptionContext);
};
