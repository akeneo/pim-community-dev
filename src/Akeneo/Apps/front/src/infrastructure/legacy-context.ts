import * as React from 'react';
import {ViewBuilder} from './pim-view/view-builder';

interface ContextValues {
    viewBuilder?: ViewBuilder;
}

export const LegacyContext = React.createContext<ContextValues>({});
