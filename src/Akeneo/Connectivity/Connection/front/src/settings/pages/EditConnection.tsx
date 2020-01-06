import {Formik, FormikHelpers, useFormikContext} from 'formik';
import React, {useEffect} from 'react';
import {useHistory, useParams} from 'react-router';
import styled from 'styled-components';
import {FlowType} from '../../model/flow-type.enum';
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
import {connectionUpdated, connectionWithCredentialsFetched} from '../actions/connections-actions';
import {useUpdateConnection} from '../api-hooks/use-update-connection';
import {useConnectionsState, useConnectionsDispatch} from '../connections-context';
import {ConnectionCredentials} from '../components/ConnectionCredentials';
import {ConnectionEditForm} from '../components/ConnectionEditForm';
import {useMediaUrlGenerator} from '../use-media-url-generator';
import {Connection} from '../../model/connection';

type ResultValues = {
    code: string;
    label: string;
    flow_type: FlowType;
    image: string | null;
    secret: string;
    client_id: string;
    username: string;
    password: string;
};

export type FormValues = {
    label: string;
    flowType: FlowType;
    image: string | null;
};

export type FormErrors = {
    label?: string;
    flowType?: string;
    image?: Array<string>;
};

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

export const EditConnection = () => {
    const history = useHistory();
    const connections = useConnectionsState();
    const dispatch = useConnectionsDispatch();

    const {code} = useParams<{code: string}>();
    const connection = connections[code];

    const route = useRoute('akeneo_connectivity_connection_rest_get', {code});
    useEffect(() => {
        fetchResult<ResultValues, unknown>(route).then(result => {
            if (isErr(result)) {
                history.push('/connections');
                return;
            }

            dispatch(
                connectionWithCredentialsFetched({
                    ...result.value,
                    flowType: result.value.flow_type,
                    clientId: result.value.client_id,
                })
            );
        });
    }, [dispatch, route, history]);

    const updateConnection = useUpdateConnection(code);
    const handleSubmit = async ({label, flowType, image}: FormValues, {setSubmitting}: FormikHelpers<FormValues>) => {
        const result = await updateConnection({
            code,
            label,
            flowType,
            image,
        });
        setSubmitting(false);

        if (isOk(result)) {
            dispatch(
                connectionUpdated({
                    code,
                    label,
                    flowType,
                    image,
                })
            );
        }
    };

    if (!connection) {
        return null;
    }

    const initialValues: FormValues = {
        label: connection.label,
        flowType: connection.flowType,
        image: connection.image,
    };

    return (
        <Formik initialValues={initialValues} onSubmit={handleSubmit} validate={validate} enableReinitialize>
            <>
                <HeaderContent connection={connection} />

                <PageContent>
                    <Layout>
                        <div>
                            <ConnectionEditForm connection={connection} />
                        </div>
                        <div>
                            <ConnectionCredentials
                                code={connection.code}
                                label={connection.label}
                                credentials={connection}
                            />
                        </div>
                    </Layout>
                </PageContent>
            </>
        </Formik>
    );
};

const HeaderContent = ({connection}: {connection: Connection}) => {
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
                    <BreadcrumbItem onClick={() => history.push('/connections')} isLast={false}>
                        <Translate id='pim_menu.item.connection_settings' />
                    </BreadcrumbItem>
                </Breadcrumb>
            }
            buttons={[
                <SecondaryActionsDropdownButton key={0}>
                    <DropdownLink onClick={() => history.push(`/connections/${connection.code}/delete`)}>
                        <Translate id='pim_common.delete' />
                    </DropdownLink>
                </SecondaryActionsDropdownButton>,
                <SaveButton key={1} />,
            ]}
            userButtons={
                <PimView
                    className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
                    viewName='pim-connectivity-connection-user-navigation'
                />
            }
            state={<FormState />}
            imageSrc={
                null === formik.values.image ? defaultImageUrl : generateMediaUrl(formik.values.image, 'thumbnail')
            }
        >
            {connection.label}
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
