import React, {FC} from 'react';
import {SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import styled, {keyframes} from 'styled-components';

const loadingBreath = keyframes`
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
`;

const SkeletonHelper = styled.div`
    height: 120px;
    animation: ${loadingBreath} 2s infinite;
    content: '';
    top: 0px;
    left: 0px;
    width: 100%;
    background: linear-gradient(270deg, #fdfdfd, #eee);
    background-size: 400% 400%;
    border-radius: 5px;
    margin-bottom: 20px;
`;
const SkeletonItem = styled.div`
    height: 150px;
    animation: ${loadingBreath} 2s infinite;
    content: '';
    top: 0px;
    left: 0px;
    width: 100%;
    background: linear-gradient(270deg, #fdfdfd, #eee);
    background-size: 400% 400%;
    border-radius: 5px;
    margin-bottom: 20px;
`;
const SkeletonContainer = styled.div`
    display: flex;
    justify-content: space-evenly;
    width: 100%;
    flex-direction: row;
    padding-top: 20px;
`;
const SkeletonLeftColumnContainer = styled.div`
    display: flex;
    justify-content: space-evenly;
    flex-direction: column;
    width: 50%;
    padding-right: 10px;
`;
const SkeletonRightColumnContainer = styled.div`
    display: flex;
    justify-content: space-evenly;
    flex-direction: column;
    width: 50%;
    padding-left: 10px;
`;

export const ConnectedAppsContainerIsLoading: FC = () => {
    const translate = useTranslate();

    return (
        <>
            <SkeletonHelper />
            <SectionTitle>
                <SectionTitle.Title>
                    {translate('akeneo_connectivity.connection.connect.connected_apps.list.apps.title')}
                </SectionTitle.Title>
                <SectionTitle.Spacer />
                <SectionTitle.Information></SectionTitle.Information>
            </SectionTitle>
            <SkeletonContainer>
                <SkeletonLeftColumnContainer>
                    <SkeletonItem />
                    <SkeletonItem />
                </SkeletonLeftColumnContainer>
                <SkeletonRightColumnContainer>
                    <SkeletonItem />
                    <SkeletonItem />
                </SkeletonRightColumnContainer>
            </SkeletonContainer>
        </>
    );
};
