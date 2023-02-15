import React, {FC} from 'react';
import {useHistory} from 'react-router';
import {useTranslate} from '../../../shared/translate';
import {useRouter} from '../../../shared/router/use-router';
import {useSecurity} from '../../../shared/security';
import {ApplyButton} from '../../../common';
import {useCustomAppsLimitReached} from '../../hooks/use-custom-apps-limit-reached';

export const CreateCustomAppButton: FC = () => {
    const security = useSecurity();
    const generateUrl = useRouter();
    const translate = useTranslate();
    const history = useHistory();
    const {data: isLimitReached, isLoading} = useCustomAppsLimitReached();

    if (!security.isGranted('akeneo_connectivity_connection_manage_test_apps')) {
        return null;
    }

    const handleClick = () => {
        history.push(generateUrl('akeneo_connectivity_connection_connect_custom_apps_create'));
    };
    return (
        <ApplyButton classNames={['AknButtonList-item']} onClick={handleClick} disabled={isLoading || isLimitReached}>
            {translate('akeneo_connectivity.connection.connect.custom_apps.create_button')}
        </ApplyButton>
    );
};
