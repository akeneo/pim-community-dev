import React, {DetailedHTMLProps, forwardRef, InputHTMLAttributes, PropsWithChildren, Ref} from 'react';
import {css} from 'styled-components';
import styled from '../styled-with-theme';

type InputProps = DetailedHTMLProps<InputHTMLAttributes<HTMLInputElement>, HTMLInputElement>;
type Props = PropsWithChildren<InputProps>;

const Checkbox = forwardRef(({children, ...props}: Props, ref: Ref<HTMLInputElement>) => (
    <label>
        <InputCheckbox {...(props as any)} ref={ref} type='checkbox' />
        &nbsp;
        <CheckboxLabel disabled={props.disabled}>{children}</CheckboxLabel>
    </label>
));
Checkbox.displayName = 'Checkbox';

const InputCheckbox = styled.input<{disabled?: boolean}>`
    ::before {
        ${({disabled}) =>
            disabled &&
            css`
                cursor: default !important;
            `}
    }
`;

const CheckboxLabel = styled.span<{disabled?: boolean}>`
    color: ${({theme}) => theme.color.grey140};
    cursor: pointer;

    ${({disabled, theme}) =>
        disabled &&
        css`
            color: ${theme.color.grey100};
            cursor: default;
        `}
`;

export {Checkbox};
