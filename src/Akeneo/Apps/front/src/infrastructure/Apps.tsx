import React, {useReducer} from 'react';
import {ThemeProvider} from 'styled-components';
import {AppsStateContext} from '../application/apps/app-state-context';
import {Index} from '../application/apps/pages/Index';
import {reducer} from '../application/apps/reducers/apps-reducer';
import {theme} from '../application/common/theme';
import {NotifyContext, NotifyInterface} from '../application/shared/notify';
import {RouterContext, RouterInterface} from '../application/shared/router';
import {TranslateContext, TranslateInterface} from '../application/shared/translate';
import {LegacyContext} from './legacy-context';
import {ViewBuilder} from './pim-view/view-builder';

interface Props {
    router: RouterInterface;
    translate: TranslateInterface;
    viewBuilder: ViewBuilder;
    notify: NotifyInterface;
}

export const Apps = ({router, translate, viewBuilder, notify}: Props) => {
    const [app, dispatch] = useReducer(reducer, {});

    return (
        <AppsStateContext.Provider value={[app, dispatch]}>
            <RouterContext.Provider value={router}>
                <TranslateContext.Provider value={translate}>
                    <NotifyContext.Provider value={notify}>
                        <LegacyContext.Provider
                            value={{
                                viewBuilder,
                            }}
                        >
                            <ThemeProvider theme={theme}>
                                <Index />
                            </ThemeProvider>
                        </LegacyContext.Provider>
                    </NotifyContext.Provider>
                </TranslateContext.Provider>
            </RouterContext.Provider>
        </AppsStateContext.Provider>
    );
};
