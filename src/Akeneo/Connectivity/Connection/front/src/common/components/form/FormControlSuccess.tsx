import React from 'react';
import styled from 'styled-components';
import {Translate} from '../../../shared/translate';
import {CheckGreenIcon} from '../../icons';

export const FormControlSuccess = ({success}: {success: string}) => (
    <OkStatus>
        <CheckGreenIcon />
        <Translate id={success} />
    </OkStatus>
);

const OkStatus = styled.span`
    display: flex;
    align-items: baseline;
    margin-top: 3px;
    color: #67b373;
    background-size: 20px;
    background-position: top left;
`;
