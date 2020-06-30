import React, {createContext, FC} from 'react';
import useLocales from '../hooks/useLocales';
import {Locale} from '../model';

export const LocalesContext = createContext<Locale[]>([]);
LocalesContext.displayName = 'LocalesContext';

export const LocalesContextProvider: FC = ({children}) => {
    const locales = useLocales();

    return (
        <LocalesContext.Provider value={locales}>
            {children}
        </LocalesContext.Provider>
    );
};
