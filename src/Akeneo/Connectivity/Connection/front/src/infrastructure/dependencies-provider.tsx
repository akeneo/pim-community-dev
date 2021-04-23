import React, {PropsWithChildren, ElementType} from 'react';
import {NotifyContext, NotifyInterface} from '../shared/notify';
import {RouterContext, RouterInterface} from '../shared/router';
import {SecurityContext} from '../shared/security/security-context';
import {Security as SecurityInterface} from '../shared/security/security.interface';
import {TranslateContext, TranslateInterface} from '../shared/translate';
import {UserContext, UserInterface} from '../shared/user';
import {LegacyContext} from './legacy-context';
import {ViewBuilder} from './pim-view/view-builder';

interface Props {
    router: RouterInterface;
    translate: TranslateInterface;
    viewBuilder: ViewBuilder;
    notify: NotifyInterface;
    user: UserInterface;
    security: SecurityInterface;
}

const DependenciesProvider = ({children, ...dependencies}: PropsWithChildren<Props>) => (
    <RouterContext.Provider value={dependencies.router}>
        <TranslateContext.Provider value={dependencies.translate}>
            <NotifyContext.Provider value={dependencies.notify}>
                <LegacyContext.Provider
                    value={{
                        viewBuilder: dependencies.viewBuilder,
                    }}
                >
                    <UserContext.Provider value={dependencies.user}>
                        <SecurityContext.Provider value={dependencies.security}>{children}</SecurityContext.Provider>
                    </UserContext.Provider>
                </LegacyContext.Provider>
            </NotifyContext.Provider>
        </TranslateContext.Provider>
    </RouterContext.Provider>
);

export const withDependencies = (Component: ElementType) => {
    return ({dependencies, ...props}: {dependencies: Props}) => (
        <DependenciesProvider {...dependencies}>
            <Component {...props} />
        </DependenciesProvider>
    );
};
