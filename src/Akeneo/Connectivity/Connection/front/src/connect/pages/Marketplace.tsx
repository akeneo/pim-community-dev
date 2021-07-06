import React, {FC, useContext, useEffect, useState} from 'react';
import {Breadcrumb, AppIllustration, SectionTitle, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '../../shared/translate';
import {PageHeader, PageContent} from '../../common';
import {UserButtons} from '../../shared/user';
import styled from 'styled-components';
import {useRouter} from '../../shared/router/use-router';
import {UserContext} from '../../shared/user';
import {useHistory} from 'react-router';
import MarketplaceHelper from '../components/MarketplaceHelper';
import {useFetchExtensions} from '../hooks/use-fetch-extensions';
import {MarketplaceCard, Grid as CardGrid} from '../components/MarketplaceCard';
import {Extension} from '../../model/extension';

const EmptyContainer = styled.section`
    text-align: center;
    padding: 40px;
`;

const EmptyMessage = styled.p`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('big')};
`;

export const Marketplace: FC = () => {
    const translate = useTranslate();
    const user = useContext(UserContext);
    const history = useHistory();
    const generateUrl = useRouter();
    const {extensions} = useFetchExtensions();
    const dashboardHref = `#${generateUrl('akeneo_connectivity_connection_audit_index')}`;
    const [userProfile, setUserProfile] = useState<string | null>(null);

    useEffect(() => {
        const profile = user.get<string | null>('profile');
        if (null === profile) {
            history.push('/connect/marketplace/profile');
        } else {
            setUserProfile(profile);
        }
    }, [user]);

    if (null === userProfile) {
        return null;
    }

    if (undefined === extensions) {
        return null;
    }

    const extensionList = extensions.extensions.map((extension: Extension) => (
        <MarketplaceCard key={extension.id} extension={extension} />
    ));

    const breadcrumb = (
        <Breadcrumb>
            <Breadcrumb.Step href={dashboardHref}>{translate('pim_menu.tab.connect')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.marketplace')}</Breadcrumb.Step>
        </Breadcrumb>
    );

    return (
        <>
            <PageHeader breadcrumb={breadcrumb} userButtons={<UserButtons />}>
                {translate('pim_menu.item.marketplace')}
            </PageHeader>

            <PageContent>
                <MarketplaceHelper count={extensions.total} />
                <SectionTitle>
                    <SectionTitle.Title>
                        {translate('akeneo_connectivity.connection.connect.marketplace.extensions.title')}
                    </SectionTitle.Title>
                    <SectionTitle.Spacer />
                    <SectionTitle.Information>
                        {translate(
                            'akeneo_connectivity.connection.connect.marketplace.extensions.total',
                            {
                                total: extensions.total.toString(),
                            },
                            extensions.total
                        )}
                    </SectionTitle.Information>
                </SectionTitle>
                {extensions.total === 0 ? (
                    <EmptyContainer>
                        <AppIllustration size={128} />
                        <EmptyMessage>
                            {translate('akeneo_connectivity.connection.connect.marketplace.extensions.empty')}
                        </EmptyMessage>
                    </EmptyContainer>
                ) : (
                    <CardGrid> {extensionList} </CardGrid>
                )}
            </PageContent>
        </>
    );
};
