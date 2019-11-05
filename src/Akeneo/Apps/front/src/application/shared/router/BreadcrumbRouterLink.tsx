import React, {PropsWithChildren, useContext} from 'react';
import {BreadcrumbItem} from '../../common';
import {RouterContext} from '../../shared/router';

interface Props {
    route: string;
}

export const BreadcrumbRouterLink = ({children: label, route}: PropsWithChildren<Props>) => {
    const router = useContext(RouterContext);

    return <BreadcrumbItem onClick={() => router.redirect(router.generate(route))}>{label}</BreadcrumbItem>;
};
