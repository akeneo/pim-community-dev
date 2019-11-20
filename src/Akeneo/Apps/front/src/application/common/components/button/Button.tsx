import React from 'react';
import {PropsWithChildren} from 'react';

export interface Props {
    onClick: () => void;
    count?: number;
    disabled?: boolean;
    classNames?: string[];
}

export const Button = ({
    children,
    onClick,
    count,
    disabled = 0 === count,
    classNames = [],
}: PropsWithChildren<Props>) => {
    classNames.push('AknButton');
    if (disabled) {
        classNames.push('AknButton--disabled');
    }

    return (
        <button type='button' onClick={onClick} className={classNames.join(' ')} disabled={disabled}>
            {children}
            {undefined !== count && <span className='AknButton--withSuffix'>{count}</span>}
        </button>
    );
};
