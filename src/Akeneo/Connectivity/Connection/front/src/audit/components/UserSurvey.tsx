import React from 'react';
import styled from 'styled-components';
import {Translate} from '../../shared/translate';
import {PropsWithTheme} from '../../common/theme';
import surveyImageUrl from '../../common/assets/illustrations/user-survey.svg';
import {ApplyButton} from '../../common/components';

const Title = styled.div`
    color: ${({theme}: PropsWithTheme) => theme.color.grey140};
    font-size: 28px;
`;
const Content = styled.div`
    color: ${({theme}: PropsWithTheme) => theme.color.grey120};
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.big};
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

export interface Props {
    title: string;
    content: string;
    buttonLabel: string;
    link: string;
}

export const UserSurvey = ({title, content, buttonLabel, link}: Props) => {
    const handleClick = () => {
        const win = window.open(link, '_blank');
        if (null !== win) {
            win.focus();
        }
    };

    return (
        <Container>
            <Illustration src={surveyImageUrl} alt={'ziggy illustration'} />
            <Title>
                <Translate id={title} />
            </Title>
            <Content>
                <Translate id={content} />
            </Content>
            <ApplyButton onClick={handleClick}>
                <Translate id={buttonLabel} />
            </ApplyButton>
        </Container>
    );
};
