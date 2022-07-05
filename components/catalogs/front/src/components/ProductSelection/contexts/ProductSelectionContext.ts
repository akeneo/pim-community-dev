import {createContext, useContext} from 'react';
import {ProductSelectionAction} from '../reducers/ProductSelectionReducer';

type ProductSelectionContext = (action: ProductSelectionAction) => void;

/* istanbul ignore next */
const defaultContext: ProductSelectionContext = () => null;

export const ProductSelectionContext = createContext<ProductSelectionContext>(defaultContext);

export const useProductSelectionContext = (): ProductSelectionContext => useContext(ProductSelectionContext);
