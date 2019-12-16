import React, {ComponentPropsWithoutRef, useContext} from 'react';
import {BreadcrumbItem} from '../../common';
import {RouterContext} from '../../shared/router';

interface Props {
    route: string;
}

export const BreadcrumbRouterLink = ({
    children: label,
    route,
}: Props & ComponentPropsWithoutRef<typeof BreadcrumbItem>) => {
    const router = useContext(RouterContext);

    return <BreadcrumbItem onClick={() => router.redirect(router.generate(route))}>{label}</BreadcrumbItem>;
};
