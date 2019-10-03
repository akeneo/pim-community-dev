import * as React from 'react';
import {RouterContext} from './router-context';
import {Router} from './router.interface';

interface Props {
    router: Router;
}

export const withRouter = <P extends Props>(WrappedComponent: React.ComponentType<P>) => (
    props: Pick<P, Exclude<keyof P, 'router'>>
) => {
    const router = React.useContext(RouterContext);
    const propsWithRouter = {
        ...props,
        router,
    } as P; // Force cast to the correct type, awaiting a solution for ts(3222).

    return <WrappedComponent {...propsWithRouter} />;
};
