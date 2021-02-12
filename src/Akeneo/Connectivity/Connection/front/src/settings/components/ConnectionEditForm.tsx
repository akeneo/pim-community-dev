import {Helper, SectionTitle} from 'akeneo-design-system';
import {useFormikContext} from 'formik';
import React from 'react';
import styled from 'styled-components';
import {Checkbox, FormGroup, FormInput} from '../../common';
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
            <SectionTitle>
                <SectionTitle.Title>
                    <Translate id='akeneo_connectivity.connection.edit_connection.subtitle' />
                </SectionTitle.Title>
            </SectionTitle>

            <br />

            <Container>
                <FormGroup controlId='code' label='akeneo_connectivity.connection.connection.code'>
                    <FormInput type='text' defaultValue={connection.code} disabled />
                </FormGroup>

                <FormGroup
                    controlId='label'
                    label='akeneo_connectivity.connection.connection.label'
                    helpers={[
                        errors.label && (
                            <Helper inline level='error'>
                                <Translate id={errors.label} />
                            </Helper>
                        ),
                    ]}
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
                    helpers={[
                        <Helper inline level='info' key={0}>
                            <FlowTypeHelper />
                        </Helper>,
                    ]}
                >
                    <FlowTypeSelect value={values.flowType} onChange={handleFlowTypeChange} />
                </FormGroup>

                <FormGroup
                    helpers={[
                        isAuditForbidden(values.flowType) && (
                            <Helper inline level='info'>
                                <AuditableHelper />
                            </Helper>
                        ),
                    ]}
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
                    helpers={[
                        errors.image && (
                            <Helper inline level='error'>
                                <Translate id={errors.image} />
                            </Helper>
                        ),
                    ]}
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
