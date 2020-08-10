import React, { ReactNode, Ref } from 'react';
import styled from 'styled-components'

type sizeMode = 'small' | 'large';

type CoreButtonProps = {
    ariaLabel?: string,
    ariaLabelledBy?: string,
    ariaDescribedBy?: string,
    children: ReactNode,
    sizeMode?: sizeMode,

} & React.ButtonHTMLAttributes<HTMLButtonElement>

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
const CoreButton =
    React.forwardRef<HTMLButtonElement, CoreButtonProps>(function CoreButton(
        {
            ariaDescribedBy,
            ariaLabel,
            ariaLabelledBy,
            children,
            disabled,
            onKeyDown,
            sizeMode,
            type = 'button',
            ...rest
        },
        forwardedRef: Ref<HTMLButtonElement>) {
        // https://www.w3.org/TR/wai-aria-practices/#button
        const handleKeyDown = (event: React.KeyboardEvent<HTMLButtonElement>) => {
            if (onKeyDown && (event.keyCode === 32 || event.keyCode === 13)) {
                onKeyDown(event)
            }
        }
        return <BasicButton
            aria-disabled={disabled}
            aria-describedby={ariaDescribedBy}
            aria-labelledby={ariaLabelledBy}
            aria-label={ariaLabel}
            disabled={disabled}
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
