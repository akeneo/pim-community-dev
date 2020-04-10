import React, { ReactNode, Ref } from 'react';
import styled from 'styled-components';
import { color, fontSize } from '../../../theme/akeneoTheme';

type sizeMode = 'small' | 'large';

type CoreButtonProps = {
    ariaLabel?: string;
    ariaLabelledBy?: string;
    ariaDescribedBy?: string;
    children: ReactNode;
    sizeMode?: sizeMode;
} & React.ButtonHTMLAttributes<HTMLButtonElement>;

const getHeight = ({ sizeMode }: CoreButtonProps): string => {
    if (sizeMode === 'small') {
        return '20px';
    }
    return '32px';
};
const getLineHeight = ({ sizeMode }: CoreButtonProps): string => {
    if (sizeMode === 'small') {
        return '18px';
    }
    return '30px';
};

const getPadding = ({ sizeMode }: CoreButtonProps): string => {
    if (sizeMode === 'small') {
        return '0 10px';
    }
    return '0 15px';
};

const BasicButton = styled.button<CoreButtonProps>`
    border-radius: 16px;
    cursor: pointer;
    font-size: ${fontSize.default};
    font-weight: 400;
    height: ${getHeight};
    line-height: ${getLineHeight};
    text-transform: uppercase;
    padding: ${getPadding};
    &:disabled {
        cursor: not-allowed;
    }
    &:focus {
        border-color: ${color.blue100};
    }
`;
const CoreButton = React.forwardRef<HTMLButtonElement, CoreButtonProps>(
    function CoreButton(
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
        forwardedRef: Ref<HTMLButtonElement>
    ) {
        // https://www.w3.org/TR/wai-aria-practices/#button
        const handleKeyDown = (
            event: React.KeyboardEvent<HTMLButtonElement>
        ) => {
            if (onKeyDown && (event.keyCode === 32 || event.keyCode === 13)) {
                onKeyDown(event);
            }
        };
        return (
            <BasicButton
                aria-describedby={ariaDescribedBy}
                aria-disabled={disabled}
                aria-label={ariaLabel}
                aria-labelledby={ariaLabelledBy}
                disabled={disabled}
                onKeyDown={handleKeyDown}
                ref={forwardedRef}
                role='button'
                sizeMode={sizeMode}
                type={type}
                {...rest}>
                {children}
            </BasicButton>
        );
    }
);

export { CoreButton };
