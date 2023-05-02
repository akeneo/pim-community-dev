import React, {PropsWithChildren, ElementType} from 'react';
import {NotifyContext, NotifyInterface} from '../shared/notify';
import {RouterContext, RouterInterface} from '../shared/router';
import {SecurityContext} from '../shared/security/security-context';
import {Security as SecurityInterface} from '../shared/security/security.interface';
import {TranslateContext, TranslateInterface} from '../shared/translate';
import {UserContext, UserInterface} from '../shared/user';
import {LegacyContext} from './legacy-context';
import {ViewBuilder} from './pim-view/view-builder';
import {FeatureFlagsContext, FeatureFlags} from '../shared/feature-flags';
import {PermissionFormRegistryContext, PermissionFormRegistry} from '../shared/permission-form-registry';
import {
    DependenciesContext,
    Translate as SharedTranslate,
    FeatureFlags as SharedFeatureFlags,
    systemConfiguration,
} from '@akeneo-pim-community/shared';

interface Props {
    router: RouterInterface;
    translate: TranslateInterface;
    viewBuilder: ViewBuilder;
    notify: NotifyInterface;
    user: UserInterface;
    security: SecurityInterface;
    featureFlags: FeatureFlags;
    permissionFormRegistry: PermissionFormRegistry;
}

const DependenciesProvider = ({children, ...dependencies}: PropsWithChildren<Props>) => (
    <DependenciesContext.Provider
        value={{
            translate: dependencies.translate as SharedTranslate,
            featureFlags: dependencies.featureFlags as SharedFeatureFlags,
            systemConfiguration,
            user: dependencies.user,
        }}
    >
        <RouterContext.Provider value={dependencies.router}>
            <TranslateContext.Provider value={dependencies.translate}>
                <NotifyContext.Provider value={dependencies.notify}>
                    <LegacyContext.Provider
                        value={{
                            viewBuilder: dependencies.viewBuilder,
                        }}
                    >
                        <UserContext.Provider value={dependencies.user}>
                            <SecurityContext.Provider value={dependencies.security}>
                                <FeatureFlagsContext.Provider value={dependencies.featureFlags}>
                                    <PermissionFormRegistryContext.Provider value={dependencies.permissionFormRegistry}>
                                        {children}
                                    </PermissionFormRegistryContext.Provider>
                                </FeatureFlagsContext.Provider>
                            </SecurityContext.Provider>
                        </UserContext.Provider>
                    </LegacyContext.Provider>
                </NotifyContext.Provider>
            </TranslateContext.Provider>
        </RouterContext.Provider>
    </DependenciesContext.Provider>
);

export const withDependencies = (Component: ElementType) => {
    return ({dependencies, ...props}: {dependencies: Props}) => (
        <DependenciesProvider {...dependencies}>
            <Component {...props} />
        </DependenciesProvider>
    );
};
