import React from 'react';
import {CellInputsMapping, CellMatchersMapping} from '../product';

type CellMappingContextState = {
  cellMatchersMapping: CellMatchersMapping;
  cellInputsMapping: CellInputsMapping;
};

export const CellMappingContext = React.createContext<CellMappingContextState>({
  cellMatchersMapping: {},
  cellInputsMapping: {},
});

export const useCellMatchersMapping = () => {
  return React.useContext(CellMappingContext).cellMatchersMapping;
};

export const useCellInputsMapping = () => {
  return React.useContext(CellMappingContext).cellInputsMapping;
};
