import React, { FunctionComponent, Ref } from 'react';
import styled from 'styled-components'

// Font style	body-small
const BasicButton = styled.button`
    font-size: 13px;
    font-weight: regular;
    text-transform: uppercase;
    padding: 0 15px;
    border-radius: 16px;
    height: 32px;
    line-height: 32px;
`
const CoreButton: FunctionComponent<React.ButtonHTMLAttributes<HTMLButtonElement>> =
    React.forwardRef(function CoreButton(
        { children, className, ...rest },
        forwardedRef: Ref<HTMLButtonElement>) {
        return <BasicButton className={className} ref={forwardedRef} {...rest}>{children}</BasicButton>
    })

export { CoreButton };
