import React, {FormEvent} from 'react';
import styled, {css} from 'styled-components';
import {CheckIcon} from '../../icons/CheckIcon';

/**
 * @TODO use blue20 instead of #dee9f4
 * @TODO use blue40 instead of #bdd3e9
 * @TODO use blue100 instead of #5992c7
 * @TODO use grey60 instead of #f9f9fb
 * @TODO use grey100 instead of #a1a9b7
 * @TODO use grey140 instead of #11324d
*/

const CheckboxContainer = styled.div < {checked: boolean, readOnly: boolean } > `
    background-color: transparent;
    height: 20px;
    width: 20px;
    border: 1px solid #5992c7;
    border-radius: 3px;
    display: inline-block;

    ${props =>
        props.checked && css`
            background-color: #5992c7
        `
    }

    ${props =>
        props.checked && props.readOnly && css`
            background-color: #dee9f4
            border-color: #bdd3e9
        `
    }

    ${props =>
        !props.checked && props.readOnly && css`
            background-color: #f9f9fb
            border-color: #a1a9b7
        `
    }
`;

const LabelContainer = styled.div < {readOnly: boolean} > `
    font-color: "#11324d";
    font-weight: 400;
    font-size: 15px;
    padding-left: 10px;
    display: inline-block;

    ${props =>
        props.readOnly && `
            font-color: "#a1a9b7";
        `
    }
`;

type CheckboxProps = {
    checked: boolean,
    readOnly: boolean,
    label?: string,
    onChange?: (value: boolean) => void,
};

/**
 * The checkboxes are applied when users can select all, several, or none of the options from a given list.
 */
const Checkbox = ({label, checked, onChange, readOnly = false}: CheckboxProps) => {
    const handleChange = (e: FormEvent<HTMLDivElement>) => onChange && !readOnly && onChange(!checked);

    console.log(checked);
    console.log(readOnly);

    return (
        <label>
            <CheckboxContainer onClick={handleChange} checked={checked} readOnly={readOnly}>
                {checked ? (
                    <CheckIcon height={20} width={20}/>
                ) : null}
            </CheckboxContainer>
            {label ? (
                <LabelContainer readOnly={readOnly}>
                    {label}
                </LabelContainer>
            ) : null}
        </label>
    );
};

export {Checkbox};
