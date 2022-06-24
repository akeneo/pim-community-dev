import {useContext} from 'react';
import {ToastContext} from './ToastContext';

export const useToaster = () => {
    const notify = useContext(ToastContext);
    if (null === notify) {
        throw new Error();
    }

    return notify;
};
