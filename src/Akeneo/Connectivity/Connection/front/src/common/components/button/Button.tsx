import React, {ButtonHTMLAttributes, DetailedHTMLProps, forwardRef, ReactNode, Ref} from 'react';

export type Props = DetailedHTMLProps<ButtonHTMLAttributes<HTMLButtonElement>, HTMLButtonElement> & {
    children?: ReactNode;
    count?: number;
    classNames?: string[];
};

export const Button = forwardRef(({children, count, classNames = [], ...props}: Props, ref: Ref<HTMLButtonElement>) => {
    classNames.push('AknButton');
    if (props.disabled) {
        classNames.push('AknButton--disabled');
    }

    return (
        <button type='button' {...(props as any)} ref={ref} className={`${props.className} ${classNames.join(' ')}`}>
            {children}
            {undefined !== count && <span className='AknButton--withSuffix'>{count}</span>}
        </button>
    );
});
