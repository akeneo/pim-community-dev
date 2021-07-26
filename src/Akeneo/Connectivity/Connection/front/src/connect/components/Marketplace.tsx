import React, {FC, useRef} from 'react';
import MarketplaceHelper from './MarketplaceHelper';
import {ArrowSimpleUpIcon, getColor, IconButton} from 'akeneo-design-system';
import {MarketplaceCard} from './MarketplaceCard';
import {Extension, Extensions} from '../../model/extension';
import {useTranslate} from '../../shared/translate';
import styled from 'styled-components';
import {useDisplayScrollTopButton} from '../../shared/scroll/hooks/useDisplayScrollTopButton';
import findScrollParent from '../../shared/scroll/utils/findScrollParent';
import {App, Apps} from '../../model/app';
import {Section} from './Section';
import {ActivateAppButton} from './ActivateAppButton';

const ScrollToTop = styled(IconButton)`
    position: fixed;
    bottom: 40px;
    right: 40px;
    width: 38px;
    height: 38px;
    border-radius: 50%;

    background-color: ${getColor('brand', 100)};
    color: ${getColor('white')};

    &:hover:not([disabled]) {
        background-color: ${getColor('brand', 120)};
    }
`;

type Props = {
    extensions: Extensions;
    apps: Apps;
};

export const Marketplace: FC<Props> = ({extensions, apps}) => {
    const translate = useTranslate();
    const ref = useRef(null);
    const scrollContainer = findScrollParent(ref.current);
    const displayScrollButton = useDisplayScrollTopButton(ref);
    const extensionsList = extensions.extensions.map((extension: Extension) => (
        <MarketplaceCard key={extension.id} item={extension} />
    ));
    const appsList = apps.apps.map((app: App) => (
        <MarketplaceCard
            key={app.id}
            item={app}
            additionalActions={[<ActivateAppButton key={1} url={app.activate_url} />]}
        />
    ));
    const handleScrollTop = () => {
        scrollContainer.scrollTo(0, 0);
    };

    return (
        <>
            <div ref={ref} />
            <MarketplaceHelper count={extensions.total + apps.total} />

            <Section
                title={translate('akeneo_connectivity.connection.connect.marketplace.apps.title')}
                information={translate(
                    'akeneo_connectivity.connection.connect.marketplace.apps.total',
                    {
                        total: apps.total.toString(),
                    },
                    apps.total
                )}
                emptyMessage={translate('akeneo_connectivity.connection.connect.marketplace.apps.empty')}
            >
                {appsList}
            </Section>
            <Section
                title={translate('akeneo_connectivity.connection.connect.marketplace.extensions.title')}
                information={translate(
                    'akeneo_connectivity.connection.connect.marketplace.extensions.total',
                    {
                        total: extensions.total.toString(),
                    },
                    extensions.total
                )}
                emptyMessage={translate('akeneo_connectivity.connection.connect.marketplace.extensions.empty')}
            >
                {extensionsList}
            </Section>

            {displayScrollButton && (
                <ScrollToTop
                    onClick={handleScrollTop}
                    title={translate('akeneo_connectivity.connection.connect.marketplace.scroll_to_top')}
                    icon={<ArrowSimpleUpIcon />}
                />
            )}
        </>
    );
};
