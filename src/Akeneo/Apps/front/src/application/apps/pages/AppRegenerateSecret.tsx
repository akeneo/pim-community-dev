import React, {useContext} from 'react';
import {useHistory, useParams} from 'react-router';
import styled from 'styled-components';
import {GreyButton, ImportantButton, Modal} from '../../common';
import {PropsWithTheme} from '../../common/theme';
import {fetchResult} from '../../shared/fetch-result';
import {isErr} from '../../shared/fetch-result/result';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useRoute} from '../../shared/router';
import {Translate, TranslateContext} from '../../shared/translate';

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
        const result = await fetchResult<undefined, undefined>(url, {
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
            <Link href={translate('akeneo_apps.regenerate_secret.link_url')}>
                <Translate id='akeneo_apps.regenerate_secret.link' />
            </Link>
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

const Link = styled.a`
    color: ${({theme}: PropsWithTheme) => theme.color.blue};
    text-decoration: underline;
`;
