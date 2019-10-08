import * as React from 'react';
import {Index} from '../application/apps/pages/Index';
import {RouterContext, RouterInterface} from '../application/shared/router';
import {TranslateContext, TranslateInterface} from '../application/shared/translate';
import {composeProviders} from './compose-providers';

interface Props {
    router: RouterInterface;
    translate: TranslateInterface;
}

export const Apps = ({router, translate}: Props) => {
    const Providers = composeProviders([RouterContext.Provider, router], [TranslateContext.Provider, translate]);

    return (
        <Providers>
            <Index />
        </Providers>
    );
};
