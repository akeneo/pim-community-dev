import React from 'react';
import {CityIllustration} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const Container = styled.div`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    flex: 1;
    margin-top: 0;
`;

type Props = {
    message: string;
};

const EmptyProductFilesList = ({message}: Props) => {
    const translate = useTranslate();

    return (
        <Container>
            <CityIllustration size={256} />
            <NoFilesText>{translate(message)}</NoFilesText>
        </Container>
    );
};

const NoFilesText = styled.div`
    margin-bottom: 30px;
`;

export {EmptyProductFilesList};
