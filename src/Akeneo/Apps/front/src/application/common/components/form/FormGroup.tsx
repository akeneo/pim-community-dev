import React, {cloneElement, ComponentProps, ReactElement, ReactNode} from 'react';
import {FormInput} from './FormInput';
import {FormLabel} from './FormLabel';
import {FormControlInfo} from './FormControlInfo';
import {FormControlError} from './FormControlError';

interface Props {
    children: ReactElement<ComponentProps<typeof FormInput>, typeof FormInput>;
    controlId?: string;
    label?: string;
    errors?: string[];
    required?: boolean;
    info?: ReactNode;
}

export const FormGroup = ({children: control, controlId, label, errors, info, required = false}: Props) => (
    <div className='AknFieldContainer'>
        {label && (
            <div className='AknFieldContainer-header'>
                <FormLabel id={controlId} label={label} required={required || control.props.required} />
            </div>
        )}

        <div className='AknFieldContainer-inputContainer'>{cloneElement(control, {id: controlId})}</div>

        {info && (
            <div className='AknFieldContainer-footer'>
                <FormControlInfo>{info}</FormControlInfo>
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
