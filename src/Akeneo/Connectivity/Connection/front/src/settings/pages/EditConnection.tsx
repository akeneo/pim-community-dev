import {Formik, FormikHelpers, useFormikContext} from 'formik';
import React, {useEffect} from 'react';
import {useHistory, useParams} from 'react-router';
import styled from 'styled-components';
import {ApplyButton, DropdownLink, PageContent, PageHeader, SecondaryActionsDropdownButton} from '../../common';
import defaultImageUrl from '../../common/assets/illustrations/NewAPI.svg';
import {Connection} from '../../model/connection';
import {FlowType} from '../../model/flow-type.enum';
import {WrongCredentialsCombinations} from '../../model/wrong-credentials-combinations';
import {fetchResult} from '../../shared/fetch-result';
import {isErr, isOk} from '../../shared/fetch-result/result';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {connectionFetched, connectionUpdated} from '../actions/connections-actions';
import {wrongCredentialsCombinationsFetched} from '../actions/wrong-credentials-combinations-actions';
import {useFetchConnection} from '../api-hooks/use-fetch-connection';
import {useUpdateConnection} from '../api-hooks/use-update-connection';
import {ConnectionCredentials} from '../components/ConnectionCredentials';
import {ConnectionEditForm} from '../components/ConnectionEditForm';
import {ConnectionPermissionsForm} from '../components/permissions/ConnectionPermissionsForm';
import {useConnectionsDispatch, useConnectionsState} from '../connections-context';
import {useMediaUrlGenerator} from '../use-media-url-generator';
import {
    useWrongCredentialsCombinationsDispatch,
    useWrongCredentialsCombinationsState,
} from '../wrong-credentials-combinations-context';
import {Breadcrumb} from 'akeneo-design-system';
import {UserButtons} from '../../shared/user';

export type FormValues = {
    label: string;
    flowType: FlowType;
    image: string | null;
    auditable: boolean;
    userRoleId: string;
    userGroupId: string | null;
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
    } else if (label.trim().length < 3) {
        errors.label = 'akeneo_connectivity.connection.connection.constraint.label.too_short';
    }
    return errors;
};

export const EditConnection = () => {
    const history = useHistory();
    const connections = useConnectionsState();
    const dispatch = useConnectionsDispatch();

    const wrongCredentialsCombinations = useWrongCredentialsCombinationsState();
    const dispatchCombinations = useWrongCredentialsCombinationsDispatch();

    const route = useRoute('akeneo_connectivity_connection_rest_wrong_credentials_combination_list');
    useEffect(() => {
        fetchResult<WrongCredentialsCombinations, never>(route).then(result => {
            if (isOk(result)) {
                dispatchCombinations(wrongCredentialsCombinationsFetched(result.value));
            }
        });
    }, [route, dispatchCombinations]);

    const {code} = useParams<{code: string}>();
    const connection = connections[code];

    const fetchConnection = useFetchConnection(code);
    useEffect(() => {
        let cancelled = false;
        fetchConnection().then(result => {
            if (isErr(result)) {
                history.push('/connections');
                return;
            }

            !cancelled && dispatch(connectionFetched(result.value));
        });
        return () => {
            cancelled = true;
        };
    }, [fetchConnection, dispatch, history]);

    const updateConnection = useUpdateConnection(code);
    const handleSubmit = async (
        {label, flowType, image, auditable, userRoleId, userGroupId}: FormValues,
        {setSubmitting}: FormikHelpers<FormValues>
    ) => {
        const result = await updateConnection({
            code,
            label,
            flowType,
            image,
            auditable,
            userRoleId,
            userGroupId,
        });
        setSubmitting(false);

        if (isOk(result)) {
            dispatch(
                connectionUpdated({
                    code,
                    label,
                    flowType,
                    auditable,
                    image,
                    userRoleId,
                    userGroupId,
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
        auditable: connection.auditable,
        userRoleId: connection.userRoleId,
        userGroupId: connection.userGroupId,
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
                                wrongCombination={wrongCredentialsCombinations[connection.code]}
                            />
                            <br />
                            <ConnectionPermissionsForm label={connection.label} />
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
    const systemHref = `#${useRoute('oro_config_configuration_system')}`;

    return (
        <PageHeader
            breadcrumb={
                <Breadcrumb>
                    <Breadcrumb.Step href={systemHref}>
                        <Translate id='pim_menu.tab.system' />
                    </Breadcrumb.Step>
                    <Breadcrumb.Step href={history.createHref({pathname: '/connections'})}>
                        <Translate id='pim_menu.item.connection_settings' />
                    </Breadcrumb.Step>
                    <Breadcrumb.Step>{connection.label}</Breadcrumb.Step>
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
            userButtons={<UserButtons />}
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
