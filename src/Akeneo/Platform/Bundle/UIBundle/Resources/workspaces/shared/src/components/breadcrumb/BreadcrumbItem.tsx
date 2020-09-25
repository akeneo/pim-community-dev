import React, {PropsWithChildren} from 'react';

interface Props {
    onClick?: () => void;
    isLast?: boolean;
}

const BreadcrumbItem = ({children: label, onClick, isLast}: PropsWithChildren<Props>) => {
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


export {Props as BreadcrumbItemProps, BreadcrumbItem};
