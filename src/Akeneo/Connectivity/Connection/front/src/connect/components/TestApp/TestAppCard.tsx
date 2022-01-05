import React, {FC, ReactNode} from 'react';
import styled from 'styled-components';
import {TestApp} from '../../../model/app';
import {AppIllustration, getColor, getFontSize, DeleteIcon} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';

const CardContainer = styled.div`
    padding: 20px;
    border: 1px ${getColor('grey', 40)} solid;
    display: grid;
    gap: 0 20px;
    grid-template-columns: 100px 1fr 24px;
    grid-template-rows: 1fr 50px;
    grid-template-areas:
        'logo text delete'
        'logo actions actions';
`;

const LogoContainer = styled.div`
    width: 100px;
    height: 100px;
    grid-area: logo;
    border: 1px ${getColor('grey', 40)} solid;
    display: flex;
`;

const TextInformation = styled.div`
    grid-area: text;
    max-width: 100%;
`;

const Name = styled.h1`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('big')};
    font-weight: bold;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
`;

const Author = styled.h3`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('big')};
    font-weight: normal;
    margin: 0;
    margin-bottom: 5px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
`;

const DeleteButton = styled(DeleteIcon)`
    grid-area: delete;
`;

const Actions = styled.div`
    grid-area: actions;
    justify-self: end;
    align-self: end;
    text-align: right;

    & > * {
        margin-left: 10px;
    }
`;

type Props = {
    testApp: TestApp;
    additionalActions?: ReactNode[];
};

export const TestAppCard: FC<Props> = ({testApp, additionalActions}) => {
    const translate = useTranslate();
    const author = testApp.author ?? translate('pim_user.removed_user');

    const onDelete = () => {
        //TODO delete behaviour
    };

    return (
        <CardContainer>
            <LogoContainer>
                <AppIllustration width={100} height={100} />
            </LogoContainer>
            <TextInformation>
                <Name>{testApp.name}</Name>
                <Author>
                    {translate('akeneo_connectivity.connection.connect.marketplace.card.developed_by')}
                    &nbsp;
                    {author}
                </Author>
            </TextInformation>
            <DeleteButton onClick={onDelete} />
            <Actions>{additionalActions}</Actions>
        </CardContainer>
    );
};
