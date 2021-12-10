import React, {FC} from 'react';
import {AppIllustration, Information, Link} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';

type Props = {
    count: number;
};

const ConnectedAppsContainerHelper: FC<Props> = ({count}) => {
    const translate = useTranslate();

    const title = (
        <div
            dangerouslySetInnerHTML={{
                __html: translate(
                    'akeneo_connectivity.connection.connect.connected_apps.list.helper.title',
                    {count: `<span class='AknConnectivityConnection-helper--highlight'>${count}</span>`},
                    count
                ),
            }}
        />
    );

    return (
        <Information illustration={<AppIllustration />} title={title}>
            <p>{translate('akeneo_connectivity.connection.connect.connected_apps.list.helper.description_1')}</p>
            <p>
                {translate('akeneo_connectivity.connection.connect.connected_apps.list.helper.description_2')}&nbsp;
                <Link href='https://help.akeneo.com/pim/serenity/articles/manage-your-apps.html' target='_blank'>
                    {translate('akeneo_connectivity.connection.connect.connected_apps.list.helper.link')}
                </Link>
            </p>
        </Information>
    );
};

export default ConnectedAppsContainerHelper;
