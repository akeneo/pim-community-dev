import React, { FunctionComponent, Ref } from 'react';
import styled from 'styled-components'

type sizeMode = 'small' | 'large' | undefined;
export interface CoreButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    sizeMode?: sizeMode
}


const getSizeModeValue = ({ sizeMode }: CoreButtonProps): string => {
    if (sizeMode === 'small') {
        return '20px'
    }
    return '32px'
}

// height	20px
// line-height	20px
// Font style	body-small
const BasicButton = styled.button<CoreButtonProps>`
    font-size: 13px;
    font-weight: regular;
    text-transform: uppercase;
    padding: 0 15px;
    border-radius: 16px;
    height: ${getSizeModeValue};
    line-height: ${getSizeModeValue};
`
const CoreButton: FunctionComponent<CoreButtonProps> =
    React.forwardRef(function CoreButton(
        { children, className, type = 'button', sizeMode, ...rest },
        forwardedRef: Ref<HTMLButtonElement>) {
        return <BasicButton className={className} ref={forwardedRef} sizeMode={sizeMode} {...rest}>{children}</BasicButton>
    })

export { CoreButton };
