import {Formik, FormikHelpers, useFormikContext} from 'formik';
import React, {useEffect} from 'react';
import {useHistory, useParams} from 'react-router';
import styled from 'styled-components';
import {FlowType} from '../../../domain/apps/flow-type.enum';
import {PimView} from '../../../infrastructure/pim-view/PimView';
import {ApplyButton, Breadcrumb, BreadcrumbItem, PageContent, PageHeader} from '../../common';
import imgUrl from '../../common/assets/illustrations/api.svg';
import {fetchResult} from '../../shared/fetch-result';
import {isOk} from '../../shared/fetch-result/result';
import {BreadcrumbRouterLink, useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {appUpdated, appWithCredentialsFetched} from '../actions/apps-actions';
import {useAppsState} from '../app-state-context';
import {AppCredentials} from '../components/AppCredentials';
import {AppEditForm} from '../components/AppEditForm';
import {useUpdateApp} from '../api-hooks/use-update-app';

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

const validate = ({label}: FormValues): FormErrors => {
    const errors: FormErrors = {};
    if (!label || label.trim().length === 0) {
        errors.label = 'akeneo_apps.app.constraint.label.required';
    }

    return errors;
};

export const AppEdit = () => {
    const history = useHistory();
    const [apps, dispatch] = useAppsState();

    const {code} = useParams<{code: string}>();
    const app = apps[code];

    const fetchAppUrl = useRoute('akeneo_apps_get_rest', {code});
    useEffect(() => {
        fetchResult<FetchAppData, unknown>(fetchAppUrl).then(result => {
            if (isOk(result)) {
                dispatch(
                    appWithCredentialsFetched({
                        ...result.value,
                        flowType: result.value.flow_type,
                        clientId: result.value.client_id,
                    })
                );
            } else {
                console.error('App not found!');
                history.push('/apps');
            }
        });
    }, [dispatch, fetchAppUrl, history]);

    const updateApp = useUpdateApp(code);
    const handleSubmit = async ({label, flowType}: FormValues, {setSubmitting}: FormikHelpers<FormValues>) => {
        const result = await updateApp({
            code,
            label,
            flowType,
        });
        setSubmitting(false);

        if (isOk(result)) {
            dispatch(
                appUpdated({
                    code,
                    label,
                    flowType,
                })
            );
        } else {
            console.error('Error while saving the app!');
            history.push('/apps');
        }
    };

    if (!app) {
        return null;
    }

    const initialValues: FormValues = {
        label: app.label,
        flowType: app.flowType,
    };

    return (
        <Formik initialValues={initialValues} onSubmit={handleSubmit} validate={validate} enableReinitialize>
            <>
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
                    buttons={[<SaveButton key={0} />]}
                    userButtons={
                        <PimView
                            className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
                            viewName='pim-apps-user-navigation'
                        />
                    }
                    state={<FormState />}
                    imageSrc={imgUrl}
                >
                    {app.label}
                </PageHeader>

                <PageContent>
                    <Layout>
                        <div>
                            <AppEditForm app={app} />
                        </div>
                        <div>
                            <AppCredentials code={app.code} credentials={app} />
                        </div>
                    </Layout>
                </PageContent>
            </>
        </Formik>
    );
};

const SaveButton = () => {
    const formik = useFormikContext();

    return (
        <ApplyButton
            key={0}
            onClick={() => formik.submitForm()}
            disabled={!formik.dirty || !formik.isValid || formik.isSubmitting}
            classNames={['AknButtonList-item']}
        >
            <Translate id='pim_common.save' />
        </ApplyButton>
    );
};

const FormState = () => {
    const formik = useFormikContext();

    return (
        (formik.dirty && (
            <div className='updated-status'>
                <span className='AknState'>
                    <Translate id='pim_common.entity_updated' />
                </span>
            </div>
        )) ||
        null
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
