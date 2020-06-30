import {useContext} from 'react';
import {LocalesContext} from '../contexts';

export const useLocalesContext = () => {
    const localesContext = useContext(LocalesContext);
    if (!localesContext) {
        throw new Error('[LocaleContext]: locales context has not been properly initiated');
    }

    return localesContext;
};
