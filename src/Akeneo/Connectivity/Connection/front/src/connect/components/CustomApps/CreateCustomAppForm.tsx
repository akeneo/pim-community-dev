import React, {FC, useState} from 'react';
import {Button, Field, getColor, getFontSize, Helper, Modal, TextInput} from 'akeneo-design-system';
import {useTranslate} from '../../../shared/translate';
import styled from '../../../common/styled-with-theme';
import {CustomAppCredentials} from '../../../model/Apps/custom-app-credentials';
import {CustomApp, useCreateCustomApp} from '../../hooks/use-create-custom-app';

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
    setCredentials: (credentials: CustomAppCredentials) => void;
};

export const CreateCustomAppForm: FC<Props> = ({onCancel, setCredentials}) => {
    const translate = useTranslate();
    const [customApp, setCustomApp] = useState<CustomApp>({name: '', activateUrl: '', callbackUrl: ''});
    const {mutate: createCustomApp, isLoading, error: errors} = useCreateCustomApp();
    const handleChange = (field: keyof CustomApp, value: string) => {
        setCustomApp(customApp => ({
            ...customApp,
            [field]: value,
        }));
    };

    const handleCreate = () => createCustomApp(customApp, {onSuccess: setCredentials});

    return (
        <>
            <Title>
                {translate('akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.title')}
            </Title>
            <FormHelper>
                <p>
                    {translate(
                        'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.description'
                    )}
                    <span className='cline-any cline-neutral'>&nbsp;</span>
                    <Link href={'https://api.akeneo.com/apps/create-custom-app.html'}>
                        {translate(
                            'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.link'
                        )}
                    </Link>
                </p>
            </FormHelper>
            {errors?.limitReached && <Helper level='error'>{translate(errors?.limitReached)}</Helper>}
            <Form>
                <Field
                    requiredLabel={translate('pim_common.required_label')}
                    label={translate(
                        'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.fields.name'
                    )}
                >
                    <TextInput
                        invalid={!!errors?.name}
                        onChange={newValue => handleChange('name', newValue)}
                        placeholder={translate(
                            'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.field_placeholder.name'
                        )}
                    />
                    {errors?.name ? <Helper level='error'>{translate(errors?.name)}</Helper> : null}
                </Field>
                <Field
                    requiredLabel={translate('pim_common.required_label')}
                    label={translate(
                        'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.fields.activate_url'
                    )}
                >
                    <TextInput
                        invalid={!!errors?.activateUrl}
                        onChange={newValue => handleChange('activateUrl', newValue)}
                        placeholder={translate(
                            'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.field_placeholder.activate_url'
                        )}
                    />
                    {errors?.activateUrl ? <Helper level='error'>{translate(errors?.activateUrl)}</Helper> : null}
                </Field>
                <Field
                    requiredLabel={translate('pim_common.required_label')}
                    label={translate(
                        'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.fields.callback_url'
                    )}
                >
                    <TextInput
                        invalid={!!errors?.callbackUrl}
                        onChange={newValue => handleChange('callbackUrl', newValue)}
                        placeholder={translate(
                            'akeneo_connectivity.connection.connect.custom_apps.create_modal.app_information.field_placeholder.callback_url'
                        )}
                    />
                    {errors?.callbackUrl ? <Helper level='error'>{translate(errors?.callbackUrl)}</Helper> : null}
                </Field>
            </Form>

            <Modal.BottomButtons>
                <Button onClick={onCancel} level='tertiary'>
                    {translate('pim_common.cancel')}
                </Button>
                <Button onClick={handleCreate} disabled={isLoading} level='primary'>
                    {translate('pim_common.create')}
                </Button>
            </Modal.BottomButtons>
        </>
    );
};
