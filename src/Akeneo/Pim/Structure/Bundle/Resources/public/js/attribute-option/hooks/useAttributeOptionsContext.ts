import {useContext} from 'react';
import {AttributeOptionsContext} from '../contexts';

export const useAttributeOptionsContext = () => {
    return useContext(AttributeOptionsContext);
};
