import React, {cloneElement, ComponentProps, ReactElement, ReactNode} from 'react';
import styled from 'styled-components';
import {FormControlError} from './FormControlError';
import {FormInput} from './FormInput';
import {FormLabel} from './FormLabel';

interface Props {
    children: ReactElement<ComponentProps<typeof FormInput>, typeof FormInput>;
    controlId?: string;
    label?: string;
    errors?: string[];
    required?: boolean;
    helper?: ReactNode;
}

export const FormGroup = ({children: control, controlId, label, errors, helper, required = false}: Props) => (
    <div className='AknFieldContainer'>
        {label && (
            <div className='AknFieldContainer-header'>
                <FormLabel id={controlId} label={label} required={required || control.props.required} />
            </div>
        )}

        <div className='AknFieldContainer-inputContainer'>{cloneElement(control, {id: controlId})}</div>

        {helper && (
            <div className='AknFieldContainer-footer'>
                <FormControlHelper>{helper}</FormControlHelper>
            </div>
        )}

        {errors && errors.length > 0 && (
            <div className='AknFieldContainer-footer'>
                <FormControlErrors errors={errors} />
            </div>
        )}
    </div>
);

const FormControlErrors = ({errors}: {errors: string[]}) => (
    <div className='AknFieldContainer-validationErrors'>
        {errors.map(error => (
            <FormControlError key={error} error={error} />
        ))}
    </div>
);

const FormControlHelper = styled.div`
    margin-top: 6px;
`;
