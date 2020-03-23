import React, {DetailedHTMLProps, forwardRef, InputHTMLAttributes, PropsWithChildren, Ref} from 'react';
import styled, {css} from 'styled-components';
import {PropsWithTheme} from '../theme';

type InputProps = DetailedHTMLProps<InputHTMLAttributes<HTMLInputElement>, HTMLInputElement>;
type Props = PropsWithChildren<InputProps>;

export const Checkbox = forwardRef(({children, ...props}: Props, ref: Ref<HTMLInputElement>) => (
    <label>
        <InputCheckbox {...props} ref={ref} type='checkbox' />
        &nbsp;
        <CheckboxLabel disabled={props.disabled}>{children}</CheckboxLabel>
    </label>
));

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
    color: ${({theme}: PropsWithTheme) => theme.color.grey140};
    cursor: pointer;

    ${({disabled, theme}: PropsWithTheme<{disabled?: boolean}>) =>
        disabled &&
        css`
            color: ${theme.color.grey100};
            cursor: default;
        `}
`;
