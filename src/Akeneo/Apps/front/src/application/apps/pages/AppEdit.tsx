import {FormikHelpers, useFormik} from 'formik';
import React, {useEffect, useState} from 'react';
import {useHistory, useParams} from 'react-router';
import styled from 'styled-components';
import {AppCredentials as AppCredentialsInterface} from '../../../domain/apps/app-credentials.interface';
import {App as AppInterface} from '../../../domain/apps/app.interface';
import {FlowType} from '../../../domain/apps/flow-type.enum';
import {PimView} from '../../../infrastructure/pim-view/PimView';
import {ApplyButton, Breadcrumb, BreadcrumbItem, Page, PageHeader} from '../../common';
import imgUrl from '../../common/assets/illustrations/api.svg';
import {useFetch} from '../../shared/fetch';
import {isErr, isOk} from '../../shared/fetch/result';
import {BreadcrumbRouterLink, useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {AppCredentials} from '../components/AppCredentials';
import {AppEditForm} from '../components/AppEditForm';
import {useUpdateApp} from '../use-update-app';

interface FetchAppData {
    code: string;
    label: string;
    flow_type: FlowType;
    secret: string;
    client_id: string;
    username: string;
    password: string;
}

export interface FormValues {
    label: string;
    flowType: FlowType;
}

export interface FormErrors {
    label?: string;
    flowType?: string;
}

const initialValues: FormValues = {label: '', flowType: FlowType.OTHER};

const validate = ({label}: FormValues): FormErrors => {
    const errors: FormErrors = {};
    if (!label || label.trim().length === 0) {
        errors.label = 'akeneo_apps.app.constraint.label.required';
    }

    return errors;
};

export const AppEdit = () => {
    const history = useHistory();

    const {code} = useParams<{code: string}>();

    const [app, setApp] = useState<AppInterface | undefined>();
    const [credentials, setCredentials] = useState<AppCredentialsInterface | undefined>();

    const fetchAppUrl = useRoute('akeneo_apps_get_rest', {code});
    const result = useFetch<FetchAppData>(fetchAppUrl);
    if (isErr(result)) {
        history.push('/apps');
    }

    useEffect(() => {
        if (isOk(result)) {
            setApp({
                code: result.data.code,
                label: result.data.label,
                flowType: result.data.flow_type,
            });
            setCredentials({
                clientId: result.data.client_id,
                secret: result.data.secret,
                username: result.data.username,
                password: result.data.password,
            });
        }
    }, [result]);

    const updateApp = useUpdateApp(code);
    const handleSubmit = async ({label, flowType}: FormValues, {setSubmitting}: FormikHelpers<FormValues>) => {
        const result = await updateApp({
            code,
            label,
            flowType,
        });
        setSubmitting(false);

        if (isOk(result)) {
            setApp({
                code,
                label,
                flowType,
            });
        }
    };

    const formik = useFormik({
        initialValues,
        onSubmit: handleSubmit,
        validate,
    });

    useEffect(() => {
        if (!app) {
            return;
        }

        formik.resetForm({
            values: {
                label: app.label,
                flowType: app.flowType,
            },
        });
    }, [app]);

    if (!app || !credentials) {
        return null;
    }

    return (
        <Page>
            <PageHeader
                breadcrumb={
                    <Breadcrumb>
                        <BreadcrumbRouterLink route={'oro_config_configuration_system'}>
                            <Translate id='pim_menu.tab.system' />
                        </BreadcrumbRouterLink>
                        <BreadcrumbItem onClick={() => history.push('/apps')} isLast={false}>
                            <Translate id='pim_menu.item.apps' />
                        </BreadcrumbItem>
                    </Breadcrumb>
                }
                buttons={[
                    <ApplyButton
                        key={0}
                        onClick={() => formik.submitForm()}
                        disabled={!formik.dirty || !formik.isValid || formik.isSubmitting}
                        classNames={['AknButtonList-item']}
                    >
                        <Translate id='pim_common.save' />
                    </ApplyButton>,
                ]}
                userButtons={
                    <PimView
                        className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
                        viewName='pim-apps-user-navigation'
                    />
                }
                state={
                    formik.dirty && (
                        <div className='updated-status'>
                            <span className='AknState'>
                                <Translate id='pim_common.entity_updated' />
                            </span>
                        </div>
                    )
                }
                imageSrc={imgUrl}
            >
                {app.label}
            </PageHeader>

            <Layout>
                <div>
                    <AppEditForm app={app} formik={formik} />
                </div>
                <div>
                    <AppCredentials code={app.code} credentials={credentials} />
                </div>
            </Layout>
        </Page>
    );
};

const Layout = styled.div`
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-column-gap: 40px;

    @media (max-width: 980px) {
        grid-template-columns: auto;
    }
`;
