import React from 'react';
import surveyImageUrl from '../../common/assets/illustrations/UserSurvey.svg';
import {ApplyButton} from '../../common/components';
import styled from '../../common/styled-with-theme';
import {Translate} from '../../shared/translate';

const Title = styled.div`
    color: ${({theme}) => theme.color.grey140};
    font-size: 28px;
`;
const Content = styled.div`
    color: ${({theme}) => theme.color.grey120};
    font-size: ${({theme}) => theme.fontSize.big};
    margin: 10px auto 20px;
`;
const Container = styled.div`
    width: 740px;
    margin: 10px auto;
    text-align: center;
`;
const Illustration = styled.img`
    margin: 0 auto;
    width: 128px;
`;

export const UserSurvey = () => {
    const handleClick = () => {
        const win = window.open('https://links.akeneo.com/surveys/connection-dashboard', '_blank');
        if (null !== win) {
            win.focus();
        }
    };

    return (
        <Container>
            <Illustration src={surveyImageUrl} />
            <Title>
                <Translate id='akeneo_connectivity.connection.dashboard.user_survey.title' />
            </Title>
            <Content>
                <Translate id='akeneo_connectivity.connection.dashboard.user_survey.content' />
            </Content>
            <ApplyButton onClick={handleClick}>
                <Translate id='akeneo_connectivity.connection.dashboard.user_survey.button' />
            </ApplyButton>
        </Container>
    );
};
