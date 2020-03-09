import {createContext} from 'react';
import {ViewBuilder} from 'akeneomeasure/shared/legacy/pim-view/view-builder';

export type LegacyContextValue = {
  viewBuilder?: ViewBuilder;
};

export const LegacyContext = createContext<LegacyContextValue>({});
