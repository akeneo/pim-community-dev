import React, {useContext} from 'react';
import {useHistory, useParams} from 'react-router';
import {Modal, ImportantButton, GreyButton} from '../../common';
import {fetch} from '../../shared/fetch';
import {isErr} from '../../shared/fetch/result';
import {useRoute} from '../../shared/router';
import {Translate, TranslateContext} from '../../shared/translate';
import {useNotify, NotificationLevel} from '../../shared/notify';

export const AppRegenerateSecret = () => {
    const history = useHistory();
    const notify = useNotify();
    const translate = useContext(TranslateContext);

    const {code} = useParams<{code: string}>();
    const url = useRoute('akeneo_apps_regenerate_secret_rest', {code});

    const handleRedirect = () => {
        history.push(`/apps/${code}/edit`);
    };

    const handleClick = async () => {
        const result = await fetch<undefined, undefined>(url, {
            method: 'POST',
        });

        if (isErr(result)) {
            notify(NotificationLevel.ERROR, translate('akeneo_apps.regenerate_secret.flash.error'));
        } else {
            notify(NotificationLevel.SUCCESS, translate('akeneo_apps.regenerate_secret.flash.success'));
        }

        handleRedirect();
    };

    const description = (
        <>
            <Translate id='akeneo_apps.regenerate_secret.description' />
            &nbsp;
            <a href={translate('akeneo_apps.regenerate_secret.link_url')}>
                <Translate id='akeneo_apps.regenerate_secret.link' />
            </a>
        </>
    );

    return (
        <Modal
            subTitle={<Translate id='pim_apps.apps' />}
            title={<Translate id='akeneo_apps.regenerate_secret.title' />}
            description={description}
            onCancel={handleRedirect}
        >
            <div className='AknButtonList'>
                <GreyButton onClick={handleRedirect} classNames={['AknButtonList-item']}>
                    <Translate id='pim_common.cancel' />
                </GreyButton>
                <ImportantButton onClick={handleClick} classNames={['AknButtonList-item']}>
                    <Translate id='akeneo_apps.regenerate_secret.action.regenerate' />
                </ImportantButton>
            </div>
        </Modal>
    );
};
