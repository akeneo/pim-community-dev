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

    const title = (<>
        {translate('akeneo_connectivity.connection.connect.marketplace.helper.title.1')}
        <HighlightTitle>{count}</HighlightTitle>
        {translate('akeneo_connectivity.connection.connect.marketplace.helper.title.2')}
    </>);

    return (
        <Information illustration={<ChannelsIllustration />} title={title}>
            <p>{translate('akeneo_connectivity.connection.connect.marketplace.helper.description')}</p>
            <Link href={marketplaceUrl} target='_blank'>
                {translate('akeneo_connectivity.connection.connect.marketplace.helper.link')}
            </Link>
        </Information>
    );
};

export default MarketplaceHelper;
