import React from 'react';
import {useHistory} from 'react-router';
import {Modal} from '../../common';
import {Translate} from '../../shared/translate';
import {AppCreateForm} from '../components/AppCreateForm';

export const AppCreate = () => {
    const history = useHistory();

    return (
        <Modal
            subTitle={<Translate id='pim_apps.apps' />}
            title={<Translate id='pim_apps.create_app.title' />}
            description={<Translate id='pim_apps.create_app.description' />}
            onCancel={() => history.push('/apps')}
        >
            <AppCreateForm />
        </Modal>
    );
};
