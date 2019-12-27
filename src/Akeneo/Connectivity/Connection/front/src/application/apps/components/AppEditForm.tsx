import {useFormikContext} from 'formik';
import React from 'react';
import styled from 'styled-components';
import {App} from '../../../domain/apps/app.interface';
import {FormGroup, FormInput, Section} from '../../common';
import {Translate} from '../../shared/translate';
import {FormValues} from '../pages/AppEdit';
import {FlowTypeHelper} from './FlowTypeHelper';
import ImageUploader from './ImageUploader';
import {FlowTypeSelect} from './FlowTypeSelect';

interface Props {
    app: App;
}

export const AppEditForm = ({app}: Props) => {
    const {values, handleChange, setFieldValue, errors, setFieldError} = useFormikContext<FormValues>();

    return (
        <>
            <Section title={<Translate id='pim_apps.edit_app.subtitle' />} />

            <br />

            <Container>
                <FormGroup controlId='code' label='pim_apps.app.code'>
                    <FormInput type='text' defaultValue={app.code} disabled />
                </FormGroup>

                <FormGroup
                    controlId='label'
                    label='pim_apps.app.label'
                    errors={errors.label ? [errors.label] : undefined}
                >
                    <FormInput
                        type='text'
                        name='label'
                        value={values.label}
                        onChange={handleChange}
                        required
                        maxLength={100}
                    />
                </FormGroup>

                <FormGroup controlId='flow_type' label='pim_apps.app.flow_type' info={<FlowTypeHelper />}>
                    <FlowTypeSelect
                        value={values.flowType}
                        onChange={flowType => setFieldValue('flowType', flowType)}
                    />
                </FormGroup>

                <FormGroup
                    controlId='image'
                    label='pim_apps.app.image'
                    errors={errors.image ? [errors.image] : undefined}
                >
                    <ImageUploader
                        image={values.image}
                        onChange={image => setFieldValue('image', image)}
                        onError={error => setFieldError('image', error)}
                    />
                </FormGroup>
            </Container>
        </>
    );
};

const Container = styled.div`
    width: 100%;
    max-width: 400px;
`;
