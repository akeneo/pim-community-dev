import {useFormikContext} from 'formik';
import React from 'react';
import styled from 'styled-components';
import {Checkbox, FormGroup, FormInput, InlineHelper, Section} from '../../common';
import {Connection} from '../../model/connection';
import {FlowType} from '../../model/flow-type.enum';
import {Translate} from '../../shared/translate';
import {FormValues} from '../pages/EditConnection';
import {AuditableHelper} from './AuditableHelper';
import {FlowTypeHelper} from './FlowTypeHelper';
import {FlowTypeSelect} from './FlowTypeSelect';
import {ImageUploader} from './ImageUploader';

const isAuditForbidden = (flowType: FlowType) => flowType === FlowType.OTHER;

interface Props {
    connection: Connection;
}

export const ConnectionEditForm = ({connection}: Props) => {
    const {values, handleChange, setFieldValue, errors, setFieldError} = useFormikContext<FormValues>();

    const handleFlowTypeChange = (flowType: FlowType) => {
        setFieldValue('flowType', flowType);

        if (isAuditForbidden(flowType)) {
            setFieldValue('auditable', false);
        }
    };

    return (
        <>
            <Section title={<Translate id='akeneo_connectivity.connection.edit_connection.subtitle' />} />

            <br />

            <Container>
                <FormGroup controlId='code' label='akeneo_connectivity.connection.connection.code'>
                    <FormInput type='text' defaultValue={connection.code} disabled />
                </FormGroup>

                <FormGroup
                    controlId='label'
                    label='akeneo_connectivity.connection.connection.label'
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

                <FormGroup
                    controlId='flow_type'
                    label='akeneo_connectivity.connection.connection.flow_type'
                    helper={
                        <InlineHelper info>
                            <FlowTypeHelper />
                        </InlineHelper>
                    }
                >
                    <FlowTypeSelect value={values.flowType} onChange={handleFlowTypeChange} />
                </FormGroup>

                <FormGroup
                    helper={
                        isAuditForbidden(values.flowType) && (
                            <InlineHelper info>
                                <AuditableHelper />
                            </InlineHelper>
                        )
                    }
                >
                    <Checkbox
                        name='auditable'
                        checked={values.auditable}
                        onChange={handleChange}
                        disabled={isAuditForbidden(values.flowType)}
                    >
                        <Translate id='akeneo_connectivity.connection.connection.auditable' />
                    </Checkbox>
                </FormGroup>

                <FormGroup
                    controlId='image'
                    label='akeneo_connectivity.connection.connection.image'
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
