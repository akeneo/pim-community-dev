import * as React from 'react';

export interface ItemProps {
    label: string;
    onClick?: () => void;
    isLast?: boolean;
}

export const Item = ({label, onClick, isLast}: ItemProps) => {
    const className = 'AknBreadcrumb-item' + (isLast ? ' AknBreadcrumb-item--final' : '');

    if (onClick) {
        return (
            <span title={label} onClick={onClick} className={className + ' AknBreadcrumb-item--routable'}>
                {label}
            </span>
        );
    }

    return (
        <span title={label} className={className}>
            {label}
        </span>
    );
};
