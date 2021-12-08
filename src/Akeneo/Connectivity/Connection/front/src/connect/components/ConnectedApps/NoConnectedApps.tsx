import React, {FC} from 'react';
import {AppIllustration, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import styled from '../../../common/styled-with-theme';
import {useRouter} from '../../../shared/router/use-router';

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
    const generateUrl = useRouter();
    const marketplaceUrl = `#${generateUrl('akeneo_connectivity_connection_connect_marketplace')}`;
    const marketplaceLinkAnchor = translate(
        'akeneo_connectivity.connection.connect.connected_apps.list.apps.marketplace_link_anchor'
    );

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
                            {marketplaceLink: `<a href=${marketplaceUrl}>${marketplaceLinkAnchor}</a>`}
                        ),
                    }}
                />
            </EmptyContainer>
        </>
    );
};
