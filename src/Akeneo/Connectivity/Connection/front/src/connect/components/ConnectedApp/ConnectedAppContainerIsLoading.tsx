import React, {FC} from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import styled, {keyframes} from 'styled-components';
import {PageContent, PageHeader} from '../../../common';
import {UserButtons} from '../../../shared/user';
import {useRouter} from '../../../shared/router/use-router';
import defaultImageUrl from '../../../common/assets/illustrations/NewAPI.svg';

const loadingBreath = keyframes`
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
`;

const SkeletonTabBar = styled.div`
    height: 44px;
    animation: ${loadingBreath} 2s infinite;
    content: '';
    top: 0px;
    left: 0px;
    width: 100%;
    background: linear-gradient(270deg, #fdfdfd, #eee);
    background-size: 400% 400%;
    border-radius: 5px;
    margin-bottom: 10px;
`;

const SkeletonContent = styled.div`
    height: 400px;
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

export const ConnectedAppContainerIsLoading: FC = () => {
    const translate = useTranslate();
    const generateUrl = useRouter();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const connectedAppsListHref = `#${generateUrl('akeneo_connectivity_connection_connect_connected_apps')}`;

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step href={connectedAppsListHref}>{translate('pim_menu.item.connected_apps')}</Breadcrumb.Step>
            <Breadcrumb.Step> </Breadcrumb.Step>
        </Breadcrumb>
    );

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />} imageSrc={defaultImageUrl} />

            <PageContent>
                <SkeletonTabBar />
                <SkeletonContent />
            </PageContent>
        </>
    );
};
