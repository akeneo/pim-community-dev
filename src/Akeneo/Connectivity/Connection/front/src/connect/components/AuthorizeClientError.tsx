import React, {FC} from 'react';
import styled from 'styled-components';
import {ClientErrorIllustration} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';

const FullScreen = styled.div`
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    z-index: 900;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
`;

const ErrorTexts = styled.div`
    text-align: center;
    margin-top: 38px;
`;

const ErrorMessage = styled.h3`
    color: ${({theme}) => theme.color.grey140};
    font-size: 28px;
    font-weight: normal;
    margin: 0;
    line-height: 34px;
`;

const SubText = styled.p`
    color: ${({theme}) => theme.color.grey120};
    font-size: ${({theme}) => theme.fontSize.big};
    margin: 10px;
`;

interface Props {
    error: string;
}

export const AuthorizeClientError: FC<Props> = ({error}) => {
    const translate = useTranslate();

    return (
        <FullScreen>
            <ClientErrorIllustration width={420} height={204} />
            <ErrorTexts>
                <ErrorMessage>{translate(error)}</ErrorMessage>
                <SubText>{translate('akeneo_connectivity.connection.connect.apps.error.sub_text')}</SubText>
            </ErrorTexts>
        </FullScreen>
    );
};
