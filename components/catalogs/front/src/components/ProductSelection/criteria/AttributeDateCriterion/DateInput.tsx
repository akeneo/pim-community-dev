import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from 'akeneo-design-system';
import React, {ChangeEvent, FC, InputHTMLAttributes, memo, useCallback} from 'react';

const StyledInput = styled.input<{readOnly: boolean; invalid: boolean} & AkeneoThemedProps>`
    width: 100%;
    height: 40px;
    border: 1px solid ${({invalid}) => (invalid ? getColor('red', 100) : getColor('grey', 80))};
    border-radius: 2px;
    box-sizing: border-box;
    background: ${({readOnly}) => (readOnly ? getColor('grey', 20) : getColor('white'))};
    color: ${({readOnly}) => (readOnly ? getColor('grey', 100) : getColor('grey', 140))};
    font-size: ${getFontSize('default')};
    line-height: 40px;
    padding: 0 ${({readOnly}) => (readOnly ? '35px' : '15px')} 0 15px;
    outline-style: none;
    cursor: ${({readOnly}) => (readOnly ? 'not-allowed' : 'auto')};
    ${({readOnly}) =>
        readOnly &&
        css`
            overflow: hidden;
            text-overflow: ellipsis;
        `}
    &:focus-within {
        box-shadow: 0 0 0 2px ${getColor('blue', 40)};
    }

    &::placeholder {
        opacity: 1;
        color: ${getColor('grey', 100)};
    }
`;

type Props = {
    value: string;
    onChange: (value: string) => void;
    invalid: boolean;
};

type InputProps = Omit<InputHTMLAttributes<HTMLInputElement>, keyof Props> & Props;

const DateInput: FC<InputProps> = ({value, onChange, invalid, ...props}) => {
    const handleChange = useCallback((e: ChangeEvent<HTMLInputElement>) => onChange(e.currentTarget.value), [onChange]);

    return (
        <StyledInput
            {...props}
            type='date'
            value={value}
            onChange={handleChange}
            invalid={invalid}
            pattern='[0-9]{4}-[0-9]{2}-[0-9]{2}'
        />
    );
};

export default memo(DateInput);
