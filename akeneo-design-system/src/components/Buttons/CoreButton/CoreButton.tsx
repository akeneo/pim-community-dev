import React, { ReactNode, Ref } from 'react';
import styled from 'styled-components'

type sizeMode = 'small' | 'large' | undefined;
export interface CoreButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    ariaLabel?: string,
    ariaLabelledBy?: string,
    ariaDescribedBy?: string,
    children: ReactNode,
    sizeMode?: sizeMode,
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
const CoreButton =
    React.forwardRef<HTMLButtonElement, CoreButtonProps>(function CoreButton(
        {
            ariaDescribedBy,
            ariaLabel,
            ariaLabelledBy,
            children,
            className,
            disabled,
            onClick,
            onKeyDown,
            sizeMode,
            type = 'button',
            ...rest
        },
        forwardedRef: Ref<HTMLButtonElement>) {
        const handleKeyDown = (event: React.KeyboardEvent<HTMLButtonElement>) => {
            if (onKeyDown && (event.keyCode === 32 || event.keyCode === 13)) {
                onKeyDown(event)
            }
        }
        const handleClick = (event: React.MouseEvent<HTMLButtonElement, MouseEvent>) => {
            if (onClick) {
                onClick(event)
            }
        }
        return <BasicButton
            aria-disabled={disabled}
            aria-describedby={ariaDescribedBy}
            aria-labelledby={ariaLabelledBy}
            aria-label={ariaLabel}
            className={className}
            onKeyDown={handleKeyDown}
            onClick={handleClick}
            ref={forwardedRef}
            role='button'
            sizeMode={sizeMode}
            {...rest}
        >
            {children}
        </BasicButton>
    })

export { CoreButton };
