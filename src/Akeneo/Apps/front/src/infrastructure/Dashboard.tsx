import React from 'react';
import {ThemeProvider} from 'styled-components';
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

export const Dashboard = ({router, translate, viewBuilder, notify}: Props) => {
    return (
        <RouterContext.Provider value={router}>
            <TranslateContext.Provider value={translate}>
                <NotifyContext.Provider value={notify}>
                    <LegacyContext.Provider
                        value={{
                            viewBuilder,
                        }}
                    >
                        <ThemeProvider theme={theme}>
                            <></>
                        </ThemeProvider>
                    </LegacyContext.Provider>
                </NotifyContext.Provider>
            </TranslateContext.Provider>
        </RouterContext.Provider>
    );
};
