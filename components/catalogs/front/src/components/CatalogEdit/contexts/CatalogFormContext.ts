import {createContext, useContext} from 'react';
import {CatalogFormAction} from '../reducers/CatalogFormReducer';

type CatalogFormContext = (action: CatalogFormAction) => void;

/* istanbul ignore next */
const defaultContext: CatalogFormContext = () => null;

export const CatalogFormContext = createContext<CatalogFormContext>(defaultContext);

export const useCatalogFormContext = (): CatalogFormContext => useContext(CatalogFormContext);
