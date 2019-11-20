import React from 'react';
import {ThemeProvider} from 'styled-components';
import {Index} from '../application/apps/pages/Index';
import {theme} from '../application/common/theme';
import {NotifyContext, NotifyInterface} from '../application/shared/notify';
import {RouterContext, RouterInterface} from '../application/shared/router';
import {TranslateContext, TranslateInterface} from '../application/shared/translate';
import {composeProviders} from './compose-providers';
import {LegacyContext} from './legacy-context';
import {ViewBuilder} from './pim-view/view-builder';

interface Props {
    router: RouterInterface;
    translate: TranslateInterface;
    viewBuilder: ViewBuilder;
    notify: NotifyInterface;
}

export const Apps = ({router, translate, viewBuilder, notify}: Props) => {
    const Providers = composeProviders(
        [RouterContext.Provider, router],
        [TranslateContext.Provider, translate],
        [NotifyContext.Provider, notify],
        [
            LegacyContext.Provider,
            {
                viewBuilder,
            },
        ]
    );

    return (
        <Providers>
            <ThemeProvider theme={theme}>
                <Index />
            </ThemeProvider>
        </Providers>
    );
};
