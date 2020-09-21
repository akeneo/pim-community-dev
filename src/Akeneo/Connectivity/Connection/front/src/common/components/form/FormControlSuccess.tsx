import React from 'react';
import {Translate} from '../../../shared/translate';

export const FormControlSuccess = ({error}: {error: string}) => (
    <span key={error} className='AknFieldContainer-validationError'>
        <Translate id={error} />
    </span>
);


/*
const OkStatus = styled.span`
    display: flex;
    align-items: baseline;
    margin-top: -14px;
    margin-bottom: 18px;
    color: #67b373;
    background: url('/bundles/pimui/images/icon-check-green.svg') no-repeat left center;
    padding-left: 26px;
    background-size: 20px;
    background-position: top left;
`;

*/

