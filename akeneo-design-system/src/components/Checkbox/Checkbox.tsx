import React from 'react';
import styled, {css} from 'styled-components';
import {CheckIcon} from '../../icons/CheckIcon';
import {PartialCheckIcon} from "../../icons/PartialCheckIcon";

const Container = styled.div`
    display: flex;
`;

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
    outline: none;

    ${props =>
        (props.checked) && css`
            background-color: #5992c7;
    `}

    ${props =>
        props.checked && props.readOnly && css`
            background-color: #dee9f4;
            border-color: #bdd3e9;
    `}

    ${props =>
        !props.checked && props.readOnly && css`
            background-color: #f9f9fb;
            border-color: #a1a9b7;
    `}
`;

const LabelContainer = styled.div < {readOnly: boolean} > `
    color: #11324d;
    font-weight: 400;
    font-size: 15px;
    padding-left: 10px;

    ${props =>
        props.readOnly && `
            color: #a1a9b7;
    `}
`;

type CheckboxProps = {
    /**
     * State of the Checkbox
     */
    checked: boolean,

    /**
     * Displays the value of the input, but does not allow changes.
     */
    readOnly?: boolean,

    /**
     * The undetermined state comes into play when the checkbox contains a sublist of selections,
     * some of which are selected, and others aren't.
     */
    undetermined?: boolean,

    /**
     * Provide a description of the Checkbox, the label appear on the right of the checkboxes.
     */
    label?: string,

    /**
     * The handler called when clicking on Checkbox
     */
    onChange?: (value: boolean) => void,
};

/**
 * The checkboxes are applied when users can select all, several, or none of the options from a given list.
 */
const Checkbox = ({label, checked, onChange, undetermined = false, readOnly = false}: CheckboxProps) => {
    if (undefined === onChange && false === readOnly) {
        throw new Error('A Checkbox element expect a onChange attribute if not readOnly');
    }

    const handleChange = () => onChange && !readOnly && onChange(!checked);

    return (
        <Container onClick={handleChange}>
            <CheckboxContainer checked={checked || undetermined} readOnly={readOnly}>
                { undetermined ? (
                    <PartialCheckIcon height={20} width={20}/>
                ) : checked ? (
                    <CheckIcon height={20} width={20}/>
                ) : null}
            </CheckboxContainer>
            {label ? (
                <LabelContainer readOnly={readOnly}>
                    {label}
                </LabelContainer>
            ) : null}
        </Container>
    );
};

export {Checkbox};
