import * as React from 'react';
import {BreadcrumbItem} from '../../common';
import {RouterContext} from '../../shared/router';

interface Props {
    route: string;
}

export const BreadcrumbRouterLink = ({children: label, route}: React.PropsWithChildren<Props>) => {
    const router = React.useContext(RouterContext);

    return <BreadcrumbItem onClick={() => router.redirect(router.generate(route))}>{label}</BreadcrumbItem>;
};
