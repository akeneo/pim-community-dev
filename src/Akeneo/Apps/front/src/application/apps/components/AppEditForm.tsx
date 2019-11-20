import React, {ChangeEvent, forwardRef, Ref, useEffect, useImperativeHandle, useRef, useState} from 'react';
import {App} from '../../../domain/apps/app.interface';
import {FlowType} from '../../../domain/apps/flow-type.enum';
import {Form, FormGroup, FormInput, Section} from '../../common';
import {isErr} from '../../shared/fetch/result';
import {Translate} from '../../shared/translate';
import {useUpdateApp} from '../use-update-app';
import {FlowTypeSelect} from './FlowTypeSelect';
import {FlowTypeHelper} from './FlowTypeHelper';

interface Props {
    app: App;
    onChange: ({hasUnsavedChanges, isValid}: {hasUnsavedChanges: boolean; isValid: boolean}) => void;
}

export const AppEditForm = forwardRef(({app, onChange}: Props, ref: Ref<{submit: () => void}>) => {
    const update = useUpdateApp(app.code);

    const formRef = useRef<HTMLFormElement>(null);
    const labelInputRef = useRef<HTMLInputElement>(null);

    // 'label' control
    const [labelControl, setLabelControl] = useState<{value: string; errors: string[]}>({
        value: app.label,
        errors: [],
    });
    useEffect(() => {
        setLabelControl({value: app.label, errors: []});
    }, [app.label]);

    // 'flowType' control
    const [flowTypeControl, setFlowTypeControl] = useState<{value: FlowType}>({value: app.flowType});
    useEffect(() => {
        setFlowTypeControl({value: app.flowType});
    }, [app.flowType]);

    // controls validation
    useEffect(() => {
        const input = labelInputRef.current;
        if (null === input) {
            return;
        }

        if (false === input.checkValidity() && 0 === labelControl.errors.length) {
            const errors = [];
            if (input.validity.valueMissing) {
                errors.push('akeneo_apps.app.constraint.label.required');
            }
            setLabelControl({...labelControl, errors});
        }
    }, [labelControl]);

    const handleFormChange = ({hasUnsavedChanges}: {hasUnsavedChanges: boolean}) => {
        onChange({
            hasUnsavedChanges,
            isValid: !!formRef.current && formRef.current.checkValidity(),
        });
    };

    const handleLabelChange = (event: ChangeEvent<HTMLInputElement>) => {
        setLabelControl({value: event.currentTarget.value, errors: []});
        handleFormChange({hasUnsavedChanges: true});
    };

    const handleFlowTypeChange = (flowType: FlowType) => {
        setFlowTypeControl({value: flowType});
        handleFormChange({hasUnsavedChanges: true});
    };

    const handleSubmit = async () => {
        if (!formRef.current || !formRef.current.checkValidity()) {
            return;
        }

        const result = await update({
            code: app.code,
            label: labelControl.value,
            flowType: flowTypeControl.value,
        });
        if (isErr(result)) {
            const errors = result.error.errors.filter(({name}) => name === 'label').map(({reason}) => reason);
            setLabelControl({...labelControl, errors});

            return;
        }

        handleFormChange({hasUnsavedChanges: false});
    };
    useImperativeHandle(ref, () => ({
        submit: handleSubmit,
    }));

    return (
        <>
            <Section title={<Translate id='pim_apps.edit_app.subtitle' />} />

            <br />

            <Form ref={formRef}>
                <FormGroup controlId='code' label='pim_apps.app.code'>
                    <FormInput type='text' defaultValue={app.code} disabled />
                </FormGroup>

                <FormGroup controlId='label' label='pim_apps.app.label' errors={labelControl.errors}>
                    <FormInput
                        ref={labelInputRef}
                        type='text'
                        name='label'
                        value={labelControl.value}
                        onChange={handleLabelChange}
                        required
                        maxLength={100}
                    />
                </FormGroup>

                <FormGroup controlId='flow_type' label='pim_apps.app.flow_type' info={<FlowTypeHelper />}>
                    <FlowTypeSelect value={flowTypeControl.value} onChange={handleFlowTypeChange} />
                </FormGroup>
            </Form>
        </>
    );
});
