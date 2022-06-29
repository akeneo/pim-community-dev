/* istanbul ignore file */
import {createContext, useContext} from 'react';
import {CatalogFormAction} from '../reducers/CatalogFormReducer';

type CatalogFormContext = (action: CatalogFormAction) => void;

const defaultContext: CatalogFormContext = () => null;

export const CatalogFormContext = createContext<CatalogFormContext>(defaultContext);

export const useCatalogFormContext = (): CatalogFormContext => useContext(CatalogFormContext);
