import React from 'react';
import {useTranslate} from '../../../shared/translate';
import styled from '../../../common/styled-with-theme';
import {getColor} from 'akeneo-design-system';

const Title = styled.h2`
    color: ${getColor('grey', 140)};
    font-size: 28px;
    font-weight: normal;
    line-height: 28px;
    margin: 0;
`;

export const CreateTestAppCredentials = () => {
    const translate = useTranslate();

    return (
        <>
            <Title>
                {translate('akeneo_connectivity.connection.connect.marketplace.test_apps.modal.credentials.title')}
            </Title>
        </>
    );
};
