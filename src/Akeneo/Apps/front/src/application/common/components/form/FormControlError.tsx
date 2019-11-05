import React from 'react';
import {Translate} from '../../../shared/translate';

export const FormControlError = ({error}: {error: string}) => (
    <span key={error} className='AknFieldContainer-validationError'>
        <Translate id={error} />
    </span>
);
