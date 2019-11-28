import {FormikProps} from 'formik';
import React from 'react';
import {App} from '../../../domain/apps/app.interface';
import {Form, FormGroup, FormInput, Section} from '../../common';
import {Translate} from '../../shared/translate';
import {FormValues} from '../pages/AppEdit';
import {FlowTypeHelper} from './FlowTypeHelper';
import {FlowTypeSelect} from './FlowTypeSelect';

interface Props {
    app: App;
    formik: FormikProps<FormValues>;
}

export const AppEditForm = ({app, formik: {values, handleChange, setFieldValue, errors}}: Props) => {
    return (
        <>
            <Section title={<Translate id='pim_apps.edit_app.subtitle' />} />

            <br />

            <Form>
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
            </Form>
        </>
    );
};
