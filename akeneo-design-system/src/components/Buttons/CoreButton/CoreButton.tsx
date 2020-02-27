import React, { FunctionComponent, Ref } from 'react';
import styled from 'styled-components'

type sizeMode = 'small' | 'large' | undefined;
export interface CoreButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    ariaLabel?: string,
    sizeMode?: sizeMode
}

const getSizeModeValue = ({ sizeMode }: CoreButtonProps): string => {
    if (sizeMode === 'small') {
        return '20px'
    }
    return '32px'
}

const BasicButton = styled.button<CoreButtonProps>`
    border-radius: 16px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 400;
    height: ${getSizeModeValue};
    line-height: ${getSizeModeValue};
    padding: 0 15px;
    text-transform: uppercase;
    &:disabled {
        cursor: not-allowed;
    }
`
const CoreButton: FunctionComponent<CoreButtonProps> =
    React.forwardRef(function CoreButton(
        { ariaLabel, onClick, onKeyDown, children, className, disabled, type = 'button', sizeMode, ...rest },
        forwardedRef: Ref<HTMLButtonElement>) {
        const handleKeyDown = (event: React.KeyboardEvent<HTMLButtonElement>) => {
            if (onKeyDown && (event.keyCode === 32 || event.keyCode === 13)) {
                onKeyDown(event)
            }
        }
        console.log('hello world')
        return <BasicButton
            aria-disabled={disabled}
            aria-label={ariaLabel}
            className={className}
            onKeyDown={handleKeyDown}
            ref={forwardedRef}
            role='button'
            sizeMode={sizeMode}
            {...rest}
        >
            {children}
        </BasicButton>
    })

export { CoreButton };
