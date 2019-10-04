import * as React from 'react';

type Provider<T> = [React.Provider<T>, T];

export function composeProviders<T>(...providers: Array<Provider<T>>) {
    return ({children}: { children: React.ReactElement }) =>
        providers.reduce(
            (children, [Provider, value]) => <Provider value={value}>{children}</Provider>,
            children
        );
}
