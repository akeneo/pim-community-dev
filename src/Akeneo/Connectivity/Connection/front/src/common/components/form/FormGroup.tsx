import React, {cloneElement, ComponentProps, ReactElement, ReactNode} from 'react';
import styled from 'styled-components';
import {FormInput} from './FormInput';
import {FormLabel} from './FormLabel';

interface Props {
    children: ReactElement<ComponentProps<typeof FormInput>, typeof FormInput>;
    controlId?: string;
    label?: string;
    required?: boolean;
    helpers?: ReactNode[];
}

export const FormGroup = ({children: control, controlId, label, helpers, required = false}: Props) => (
    <div className='AknFieldContainer'>
        {label && (
            <div className='AknFieldContainer-header'>
                <FormLabel id={controlId} label={label} required={required || control.props.required} />
            </div>
        )}

        <InputContainer className='AknFieldContainer-inputContainer'>
            {cloneElement(control, {id: controlId})}
        </InputContainer>

        {helpers && (
            <HelperContainer>
                {helpers.map(helper => (
                    <div className='AknFieldContainer-footer'>{helper}</div>
                ))}
            </HelperContainer>
        )}
    </div>
);

const InputContainer = styled.div`
    min-height: 28px;
`;

const HelperContainer = styled.div`
    margin-top: 6px;
`;
