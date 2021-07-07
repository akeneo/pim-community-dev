import React, {FC} from 'react';
import MarketplaceHelper from './MarketplaceHelper';
import {AppIllustration, getColor, getFontSize, SectionTitle} from 'akeneo-design-system';
import {Grid as CardGrid, MarketplaceCard} from './MarketplaceCard';
import {Extension, Extensions} from '../../model/extension';
import {useTranslate} from '../../shared/translate';
import styled from 'styled-components';

const EmptyContainer = styled.section`
    text-align: center;
    padding: 40px;
`;

const EmptyMessage = styled.p`
    color: ${getColor('grey', 140)};
    font-size: ${getFontSize('big')};
`;

type Props = {
    extensions: Extensions;
}

export const Marketplace: FC<Props> = ({extensions}) => {
    const translate = useTranslate();
    const extensionList = extensions.extensions.map((extension: Extension) => (
        <MarketplaceCard key={extension.id} extension={extension} />
    ));

    return <>
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
    </>;
}
