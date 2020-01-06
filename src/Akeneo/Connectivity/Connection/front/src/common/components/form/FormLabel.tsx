import React from 'react';
import {Translate} from '../../../shared/translate/Translate';

export const FormLabel = ({label, id, required = false}: {label: string; id?: string; required?: boolean}) => (
    <label htmlFor={id} className='AknFieldContainer-label'>
        <Translate id={label} />
        {required && (
            <>
                &nbsp;
                <Translate id='pim_common.required_label' />
            </>
        )}
    </label>
);
