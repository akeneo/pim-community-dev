import React, {FC} from 'react';
import {useLocation} from 'react-router-dom';
import {useTranslate} from '../../shared/translate';
import {Modal} from 'akeneo-design-system';
import {useHistory} from 'react-router';
import {AuthorizeClientError} from '../components/AuthorizeClientError';

export const AppAuthorizePage: FC = () => {
    const translate = useTranslate();
    const location = useLocation();
    const history = useHistory();
    const query = new URLSearchParams(location.search);
    const error = query.get('error');

    if (null !== error) {
        return <AuthorizeClientError error={error} />;
    }

    const redirectToMarketPlace = () => {
        history.push('/connect/marketplace');
    };

    return <Modal closeTitle='Close' onClose={redirectToMarketPlace}/>;
};
