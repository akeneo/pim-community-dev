import React, {FC, useState} from 'react';
import {Button, Field, getColor, getFontSize, Helper, Modal, TextInput} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import styled from '../../../common/styled-with-theme';
import {TestAppCredentials} from '../../../model/Apps/test-app-credentials';
import {useCreateTestApp, TestApp} from '../../hooks/use-create-test-app';

const Title = styled.h2`
    color: ${getColor('grey', 140)};
    font-size: 28px;
    font-weight: normal;
    line-height: 28px;
    margin: 0;
`;

const FormHelper = styled.div`
    color: ${getColor('grey', 120)};
    font-size: ${getFontSize('default')};
    font-weight: normal;
    line-height: 18px;
    margin: 17px 0 17px 0;
`;

const Link = styled.a`
    color: ${getColor('brand', 100)};
    text-decoration: underline;
`;

const Form = styled.form`
    > * {
        margin: 20px 10px 0px 0px;
    }
`;

type Props = {
    onCancel: () => void;
    setCredentials: (credentials: TestAppCredentials) => void;
};

type FormErrors = {
    name: string | null;
    activateUrl: string | null;
    callbackUrl: string | null;
};

export const CreateTestAppForm: FC<Props> = ({onCancel, setCredentials}) => {
    const translate = useTranslate();
    const [testApp, setTestApp] = useState<TestApp>({name: '', activateUrl: '', callbackUrl: ''});
    const [errors, setErrors] = useState<FormErrors>({name: null, activateUrl: null, callbackUrl: null});
    const createTestApp = useCreateTestApp();

    const onNameChange = (newName: string) => {
        setTestApp(testApp => ({
            ...testApp,
            name: newName,
        }));
    };

    const onActivateUrlChange = (newActivateUrl: string) => {
        setTestApp(testApp => ({
            ...testApp,
            activateUrl: newActivateUrl,
        }));
    };

    const onCallbackUrlChange = (newCallbackUrl: string) => {
        setTestApp(testApp => ({
            ...testApp,
            callbackUrl: newCallbackUrl,
        }));
    };

    const handleCreate = () => {
        setErrors({name: null, activateUrl: null, callbackUrl: null});
        createTestApp(testApp).then(async response => {
            if (!response.ok) {
                const {errors} = await response.json();
                errors.forEach((error: {propertyPath: string; message: string}) => {
                    setErrors(stateErrors => ({
                        ...stateErrors,
                        [error.propertyPath]: translate(error.message),
                    }));
                });
            } else {
                const {clientId, clientSecret} = await response.json();
                setCredentials({
                    clientId,
                    clientSecret,
                });
            }
        });
    };

    return (
        <>
            <Title>
                {translate('akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.title')}
            </Title>
            <FormHelper>
                <p>
                    {translate(
                        'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.description'
                    )}
                    <span className='cline-any cline-neutral'>&nbsp;</span>
                    <Link href={'https://help.akeneo.com/pim/articles/manage-your-apps.html#create-a-test-app'}>
                        {translate(
                            'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.link'
                        )}
                    </Link>
                </p>
            </FormHelper>

            <Form>
                <Field
                    requiredLabel={translate('pim_common.required_label')}
                    label={translate(
                        'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.fields.name'
                    )}
                >
                    <TextInput invalid={null !== errors.name} onChange={onNameChange} data-testid={'name-input'} />
                    {errors.name ? <Helper level='error'>{errors.name}</Helper> : null}
                </Field>
                <Field
                    requiredLabel={translate('pim_common.required_label')}
                    label={translate(
                        'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.fields.activate_url'
                    )}
                >
                    <TextInput
                        invalid={null !== errors.activateUrl}
                        onChange={onActivateUrlChange}
                        data-testid={'activate-url-input'}
                    />
                    {errors.activateUrl ? <Helper level='error'>{errors.activateUrl}</Helper> : null}
                </Field>
                <Field
                    requiredLabel={translate('pim_common.required_label')}
                    label={translate(
                        'akeneo_connectivity.connection.connect.marketplace.test_apps.modal.app_information.fields.callback_url'
                    )}
                >
                    <TextInput
                        invalid={null !== errors.callbackUrl}
                        onChange={onCallbackUrlChange}
                        data-testid={'callback-url-input'}
                    />
                    {errors.callbackUrl ? <Helper level='error'>{errors.callbackUrl}</Helper> : null}
                </Field>
            </Form>

            <Modal.BottomButtons>
                <Button onClick={onCancel} level='tertiary'>
                    {translate('pim_common.cancel')}
                </Button>
                <Button onClick={handleCreate} level='primary'>
                    {translate('pim_common.create')}
                </Button>
            </Modal.BottomButtons>
        </>
    );
};
