import React, {FC, useEffect, useState} from 'react';
import {AppIllustration, getColor, getFontSize} from 'akeneo-design-system';
import {useFetchMarketplaceUrl} from '../../hooks/use-fetch-marketplace-url';
import {useTranslate} from '../../../shared/translate';
import styled from '../../../common/styled-with-theme';

const EmptyContainer = styled.section`
    text-align: center;
    padding: 40px;
`;

const EmptyMessage = styled.p`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('title')};
    margin-bottom: 20px;
`;

const HelpMessage = styled.p`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('bigger')};
`;

export const NoConnectedApps: FC = () => {
    const translate = useTranslate();
    const fetchMarketplaceUrl = useFetchMarketplaceUrl();
    const [marketplaceUrl, setMarketplaceUrl] = useState<string>('');

    useEffect(() => {
        fetchMarketplaceUrl().then(setMarketplaceUrl);
    }, [fetchMarketplaceUrl]);

    return (
        <>
            <EmptyContainer>
                <AppIllustration size={242} />
                <EmptyMessage>
                    {translate('akeneo_connectivity.connection.connect.connected_apps.list.apps.empty')}
                </EmptyMessage>
                <HelpMessage
                    dangerouslySetInnerHTML={{
                        __html: translate(
                            'akeneo_connectivity.connection.connect.connected_apps.list.apps.check_marketplace',
                            {href: marketplaceUrl}
                        ),
                    }}
                />
            </EmptyContainer>
        </>
    );
};
