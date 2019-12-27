import React from 'react';
import {useHistory, useParams} from 'react-router';
import styled from 'styled-components';
import {GreyButton, ImportantButton, Modal} from '../../common';
import {PropsWithTheme} from '../../common/theme';
import {isOk} from '../../shared/fetch-result/result';
import {Translate, useTranslate} from '../../shared/translate';
import {appDeleted} from '../actions/apps-actions';
import {useDeleteApp} from '../api-hooks/use-delete-app';
import {useAppsState} from '../app-state-context';

export const AppDelete = () => {
    const history = useHistory();
    const translate = useTranslate();

    const {code} = useParams<{code: string}>();
    const deleteApp = useDeleteApp(code);
    const [, dispatch] = useAppsState();

    const handleClick = async () => {
        const result = await deleteApp();

        if (isOk(result)) {
            dispatch(appDeleted(code));

            history.push('/apps');
        }
    };

    const handleCancel = () => history.push(`/apps/${code}/edit`);

    const description = (
        <>
            <Translate id='akeneo_apps.delete_app.description' />
            &nbsp;
            <Link href={translate('akeneo_apps.delete_app.link_url')} target='_blank'>
                <Translate id='akeneo_apps.delete_app.link' />
            </Link>
        </>
    );

    return (
        <Modal
            subTitle={<Translate id='pim_apps.apps' />}
            title={<Translate id='akeneo_apps.delete_app.title' />}
            description={description}
            onCancel={handleCancel}
        >
            <GreyButton onClick={handleCancel} classNames={['AknButtonList-item']}>
                <Translate id='pim_common.cancel' />
            </GreyButton>
            <ImportantButton onClick={handleClick} classNames={['AknButtonList-item']}>
                <Translate id='pim_common.delete' />
            </ImportantButton>
        </Modal>
    );
};

const Link = styled.a`
    color: ${({theme}: PropsWithTheme) => theme.color.blue100};
    text-decoration: underline;
`;
