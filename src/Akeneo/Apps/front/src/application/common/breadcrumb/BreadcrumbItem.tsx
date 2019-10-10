import * as React from 'react';

export interface Props {
    onClick?: () => void;
    isLast?: boolean;
}

export const BreadcrumbItem = ({children: label, onClick, isLast}: React.PropsWithChildren<Props>) => {
    const className = 'AknBreadcrumb-item' + (isLast ? ' AknBreadcrumb-item--final' : '');

    if (onClick) {
        return (
            <span onClick={onClick} className={className + ' AknBreadcrumb-item--routable'}>
                {label}
            </span>
        );
    }

    return <span className={className}>{label}</span>;
};
