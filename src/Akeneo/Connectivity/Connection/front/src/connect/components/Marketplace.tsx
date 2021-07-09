import React, {FC, useMemo, useRef} from 'react';
import MarketplaceHelper from './MarketplaceHelper';
import {AppIllustration, ArrowUpIcon, getColor, getFontSize, IconButton, SectionTitle} from 'akeneo-design-system';
import {Grid as CardGrid, MarketplaceCard} from './MarketplaceCard';
import {Extension, Extensions} from '../../model/extension';
import {useTranslate} from '../../shared/translate';
import styled from 'styled-components';
import {useDisplayScrollTopButton} from '../../shared/scroll/hooks/useDisplayScrollTopButton';
import findScrollParent from '../../shared/scroll/utils/findScrollParent';

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
};

export const Marketplace: FC<Props> = ({extensions}) => {
    const translate = useTranslate();
    const ref = useRef(null);
    const scrollContainer = useMemo(() => findScrollParent(ref.current), [ref]);
    const displayScrollButton = useDisplayScrollTopButton(ref);
    const extensionList = extensions.extensions.map((extension: Extension) => (
        <MarketplaceCard key={extension.id} extension={extension} />
    ));
    const handleScrollTop = () => {
        scrollContainer.scrollTo(0, 0);
    };

    return (
        <>
            <div ref={ref} />
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
            {displayScrollButton && (
                <IconButton
                    level='primary'
                    ghost='borderless'
                    onClick={handleScrollTop}
                    title={'scroll top'}
                    icon={<ArrowUpIcon />}
                />
            )}

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
            <IconButton
                level='primary'
                ghost='borderless'
                onClick={handleScrollTop}
                title={'scroll top'}
                icon={<ArrowUpIcon />}
            />
        </>
    );
};
