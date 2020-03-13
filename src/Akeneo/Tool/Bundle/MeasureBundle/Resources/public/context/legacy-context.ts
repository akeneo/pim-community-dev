import {createContext} from 'react';
import {ViewBuilder} from 'akeneomeasure/bridge/legacy/pim-view/view-builder';

type LegacyContextValue = {
  viewBuilder?: ViewBuilder;
};

const LegacyContext = createContext<LegacyContextValue>({});

export {LegacyContextValue, LegacyContext};
