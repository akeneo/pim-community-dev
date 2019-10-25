import React, {forwardRef, PropsWithChildren, Ref, DetailedHTMLProps, FormHTMLAttributes} from 'react';

type Props = PropsWithChildren<DetailedHTMLProps<FormHTMLAttributes<HTMLFormElement>, HTMLFormElement>>;

export const Form = forwardRef<HTMLFormElement, Props>(({children, ...props}: Props, ref: Ref<HTMLFormElement>) => (
    <form ref={ref} className='AknFormContainer' {...props}>
        {children}
    </form>
));
