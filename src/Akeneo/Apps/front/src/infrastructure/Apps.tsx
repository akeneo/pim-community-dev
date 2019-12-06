import React, {useReducer} from 'react';
import {AppsStateContext} from '../application/apps/app-state-context';
import {Index} from '../application/apps/pages/Index';
import {reducer} from '../application/apps/reducers/apps-reducer';
import {withContexts} from './with-contexts';

export const Apps = withContexts(() => {
    const [app, dispatch] = useReducer(reducer, {});

    return (
        <AppsStateContext.Provider value={[app, dispatch]}>
            <Index />
        </AppsStateContext.Provider>
    );
});
