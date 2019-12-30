import React from 'react';
import {useHistory} from 'react-router';
import {Modal} from '../../common';
import {Translate} from '../../shared/translate';
import {AppCreateForm} from '../components/AppCreateForm';

export const AppCreate = () => {
    const history = useHistory();

    return (
        <Modal
            subTitle={<Translate id='akeneo_connectivity.connection.connections' />}
            title={<Translate id='akeneo_connectivity.connection.create_app.title' />}
            description={<Translate id='akeneo_connectivity.connection.create_app.description' />}
            onCancel={() => history.push('/apps')}
        >
            <AppCreateForm />
        </Modal>
    );
};
