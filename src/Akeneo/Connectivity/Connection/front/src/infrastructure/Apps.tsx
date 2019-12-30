import {default as React, StrictMode, useReducer} from 'react';
import {AppsStateContext} from '../apps/app-state-context';
import {Index} from '../apps/pages/Index';
import {reducer} from '../apps/reducers/apps-reducer';
import {withContexts} from './with-contexts';

export const Apps = withContexts(() => {
    const [app, dispatch] = useReducer(reducer, {});

    return (
        <StrictMode>
            <AppsStateContext.Provider value={[app, dispatch]}>
                <Index />
            </AppsStateContext.Provider>
        </StrictMode>
    );
});
