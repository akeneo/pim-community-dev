import React, {FC, useEffect, useState} from 'react';
import {ChannelsIllustration, HighlightTitle, Information, Link} from 'akeneo-design-system';
import {useFetchMarketplaceUrl} from '../hooks/use-fetch-marketplace-url';
import {useTranslate} from '../../shared/translate';

type Props = {
    count: number;
};

const MarketplaceHelper: FC<Props> = ({count}) => {
    const translate = useTranslate();
    const fetchMarketplaceUrl = useFetchMarketplaceUrl();
    const [marketplaceUrl, setMarketplaceUrl] = useState<string>('');

    useEffect(() => {
        fetchMarketplaceUrl().then(setMarketplaceUrl);
    }, [fetchMarketplaceUrl]);

    // Color highlight count number in the title
    const translatedTitle = translate(
        'akeneo_connectivity.connection.connect.marketplace.helper.title',
        {count: '____'},
        count
    );
    let decoratedTitle = <>{translatedTitle}</>;
    if (count > 0) {
        const translatedParts = translatedTitle.split('____');
        decoratedTitle = (
            <>
                {translatedParts[0]}
                <HighlightTitle>{count}</HighlightTitle>
                {translatedParts[1]}
            </>
        );
    }

    return (
        <Information illustration={<ChannelsIllustration />} title={decoratedTitle}>
            <p>{translate('akeneo_connectivity.connection.connect.marketplace.helper.description')}</p>
            <Link href={marketplaceUrl} target='_blank'>
                {translate('akeneo_connectivity.connection.connect.marketplace.helper.link')}
            </Link>
        </Information>
    );
};

export default MarketplaceHelper;
