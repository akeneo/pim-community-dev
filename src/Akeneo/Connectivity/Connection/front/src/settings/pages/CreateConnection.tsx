import React from 'react';
import {useHistory} from 'react-router';
import {Modal} from '../../common';
import {Translate} from '../../shared/translate';
import {ConnectionCreateForm} from '../components/ConnectionCreateForm';

export const CreateConnection = () => {
    const history = useHistory();

    return (
        <Modal
            subTitle={<Translate id='akeneo_connectivity.connection.connections' />}
            title={<Translate id='akeneo_connectivity.connection.create_connection.title' />}
            description={<Translate id='akeneo_connectivity.connection.create_connection.description' />}
            onCancel={() => history.push('/connections')}
        >
            <ConnectionCreateForm />
        </Modal>
    );
};
