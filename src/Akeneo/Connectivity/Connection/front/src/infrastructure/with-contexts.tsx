import React, {ElementType} from 'react';
import {ThemeProvider} from 'styled-components';
import {theme} from '../common/theme';
import {NotifyContext, NotifyInterface} from '../shared/notify';
import {RouterContext, RouterInterface} from '../shared/router';
import {SecurityContext} from '../shared/security/security-context';
import {Security as SecurityInterface} from '../shared/security/security.interface';
import {TranslateContext, TranslateInterface} from '../shared/translate';
import {UserContext, UserInterface} from '../shared/user';
import {LegacyContext} from './legacy-context';
import {ViewBuilder} from './pim-view/view-builder';

interface Props {
    dependencies: {
        router: RouterInterface;
        translate: TranslateInterface;
        viewBuilder: ViewBuilder;
        notify: NotifyInterface;
        user: UserInterface;
        security: SecurityInterface;
    };
}

export const withContexts = (Component: ElementType) => {
    return ({dependencies, ...props}: Props) => (
        <RouterContext.Provider value={dependencies.router}>
            <TranslateContext.Provider value={dependencies.translate}>
                <NotifyContext.Provider value={dependencies.notify}>
                    <LegacyContext.Provider
                        value={{
                            viewBuilder: dependencies.viewBuilder,
                        }}
                    >
                        <ThemeProvider theme={theme}>
                            <UserContext.Provider value={dependencies.user}>
                                <SecurityContext.Provider value={dependencies.security}>
                                    <Component {...props} />
                                </SecurityContext.Provider>
                            </UserContext.Provider>
                        </ThemeProvider>
                    </LegacyContext.Provider>
                </NotifyContext.Provider>
            </TranslateContext.Provider>
        </RouterContext.Provider>
    );
};
