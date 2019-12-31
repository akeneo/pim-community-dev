import {Formik, FormikHelpers, useFormikContext} from 'formik';
import React, {useEffect} from 'react';
import {useHistory, useParams} from 'react-router';
import styled from 'styled-components';
import {FlowType} from '../../domain/apps/flow-type.enum';
import {PimView} from '../../infrastructure/pim-view/PimView';
import {
    ApplyButton,
    Breadcrumb,
    BreadcrumbItem,
    DropdownLink,
    PageContent,
    PageHeader,
    SecondaryActionsDropdownButton,
} from '../../common';
import defaultImageUrl from '../../common/assets/illustrations/api.svg';
import {fetchResult} from '../../shared/fetch-result';
import {isOk, isErr} from '../../shared/fetch-result/result';
import {BreadcrumbRouterLink, useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {appUpdated, appWithCredentialsFetched} from '../actions/apps-actions';
import {useUpdateApp} from '../api-hooks/use-update-app';
import {useAppsState} from '../app-state-context';
import {AppCredentials} from '../components/AppCredentials';
import {AppEditForm} from '../components/AppEditForm';
import {useMediaUrlGenerator} from '../use-media-url-generator';
import {App} from '../../domain/apps/app.interface';

interface FetchAppData {
    code: string;
    label: string;
    flow_type: FlowType;
    image: string | null;
    secret: string;
    client_id: string;
    username: string;
    password: string;
}

export interface FormValues {
    label: string;
    flowType: FlowType;
    image: string | null;
}

export interface FormErrors {
    label?: string;
    flowType?: string;
    image?: Array<string>;
}

const validate = ({label}: FormValues): FormErrors => {
    const errors: FormErrors = {};
    if (!label || label.trim().length === 0) {
        errors.label = 'akeneo_connectivity.connection.connection.constraint.label.required';
    }
    if (label.trim().length < 3) {
        errors.label = 'akeneo_connectivity.connection.connection.constraint.label.too_short';
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
            if (isErr(result)) {
                history.push('/apps');
                return;
            }

            dispatch(
                appWithCredentialsFetched({
                    ...result.value,
                    flowType: result.value.flow_type,
                    clientId: result.value.client_id,
                })
            );
        });
    }, [dispatch, fetchAppUrl, history]);

    const updateApp = useUpdateApp(code);
    const handleSubmit = async ({label, flowType, image}: FormValues, {setSubmitting}: FormikHelpers<FormValues>) => {
        const result = await updateApp({
            code,
            label,
            flowType,
            image,
        });
        setSubmitting(false);

        if (isOk(result)) {
            dispatch(
                appUpdated({
                    code,
                    label,
                    flowType,
                    image,
                })
            );
        }
    };

    if (!app) {
        return null;
    }

    const initialValues: FormValues = {
        label: app.label,
        flowType: app.flowType,
        image: app.image,
    };

    return (
        <Formik initialValues={initialValues} onSubmit={handleSubmit} validate={validate} enableReinitialize>
            <>
                <HeaderContent app={app} />

                <PageContent>
                    <Layout>
                        <div>
                            <AppEditForm app={app} />
                        </div>
                        <div>
                            <AppCredentials code={app.code} label={app.label} credentials={app} />
                        </div>
                    </Layout>
                </PageContent>
            </>
        </Formik>
    );
};

interface HeaderProps {
    app: App;
}

const HeaderContent = ({app}: HeaderProps) => {
    const history = useHistory();
    const formik = useFormikContext<FormValues>();
    const generateMediaUrl = useMediaUrlGenerator();

    return (
        <PageHeader
            breadcrumb={
                <Breadcrumb>
                    <BreadcrumbRouterLink route={'oro_config_configuration_system'}>
                        <Translate id='pim_menu.tab.system' />
                    </BreadcrumbRouterLink>
                    <BreadcrumbItem onClick={() => history.push('/apps')} isLast={false}>
                        <Translate id='pim_menu.item.connection_settings' />
                    </BreadcrumbItem>
                </Breadcrumb>
            }
            buttons={[
                <SecondaryActionsDropdownButton key={0}>
                    <DropdownLink onClick={() => history.push(`/apps/${app.code}/delete`)}>
                        <Translate id='pim_common.delete' />
                    </DropdownLink>
                </SecondaryActionsDropdownButton>,
                <SaveButton key={1} />,
            ]}
            userButtons={
                <PimView
                    className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
                    viewName='pim-apps-user-navigation'
                />
            }
            state={<FormState />}
            imageSrc={
                null === formik.values.image ? defaultImageUrl : generateMediaUrl(formik.values.image, 'thumbnail')
            }
        >
            {app.label}
        </PageHeader>
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
