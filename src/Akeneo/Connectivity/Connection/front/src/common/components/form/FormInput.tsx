import React, {DetailedHTMLProps, InputHTMLAttributes, forwardRef, Ref} from 'react';

interface Props {
    type: 'text';
}

type InputProps = DetailedHTMLProps<InputHTMLAttributes<HTMLInputElement>, HTMLInputElement>;

export const FormInput = forwardRef(({type, id, ...props}: Props & InputProps, ref: Ref<HTMLInputElement>) => (
    <input ref={ref} type={type} className='AknTextField' id={id} {...props} />
));
