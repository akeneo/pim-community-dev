import React, {ChangeEvent, Dispatch, useRef, useEffect} from 'react';
import {FlowType} from '../../../domain/apps/flow-type.enum';
import {Form, FormGroup, FormInput} from '../../common';
import {FlowTypeHelper} from './FlowTypeHelper';
import {FlowTypeSelect} from './FlowTypeSelect';
import {FormState, Actions} from '../reducers/app-form-reducer';
import {sanitize} from '../../shared/sanitize';

interface Props {
    state: FormState;
    dispatch: Dispatch<Actions>;
}

export const AppForm = ({state, dispatch}: Props) => {
    const codeInputRef = useRef<HTMLInputElement>(null);
    const labelInputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        [codeInputRef, labelInputRef].forEach(inputRef => {
            const input = inputRef.current;
            if (null === input) {
                return;
            }

            const name = input.name;
            if (true === state.controls[name].validated) {
                return;
            }

            if (false === input.checkValidity()) {
                if (input.validity.valueMissing) {
                    dispatch({
                        type: 'ERROR',
                        name,
                        code: `akeneo_apps.constraint.${name}.required`,
                    });
                }
                if (input.validity.patternMismatch) {
                    dispatch({
                        type: 'ERROR',
                        name,
                        code: `akeneo_apps.constraint.${name}.invalid`,
                    });
                }
            }

            dispatch({
                type: 'SET_VALIDATED',
                name,
            });
        });
    }, [state, dispatch]);

    // Auto-generate a code from the label if the code is not yet defined.
    useEffect(() => {
        if (true === state.controls.code.dirty) {
            return;
        }

        const value = sanitize(state.controls.label.value);
        if (state.controls.code.value === value) {
            return;
        }

        dispatch({type: 'CHANGE', name: 'code', value, dirty: false});
    }, [state, dispatch]);

    const handleChange = (event: ChangeEvent<HTMLInputElement>) =>
        dispatch({type: 'CHANGE', name: event.currentTarget.name, value: event.currentTarget.value});

    const handleFlowTypeSelect = (flowType: FlowType) => dispatch({type: 'CHANGE', name: 'flow_type', value: flowType});

    return (
        <Form>
            <FormGroup controlId='label' label='pim_apps.app.label' errors={Object.keys(state.controls.label.errors)}>
                <FormInput
                    ref={labelInputRef}
                    type='text'
                    name='label'
                    value={state.controls.label.value}
                    onChange={handleChange}
                    required
                    maxLength={100}
                />
            </FormGroup>

            <FormGroup controlId='code' label='pim_apps.app.code' errors={Object.keys(state.controls.code.errors)}>
                <FormInput
                    ref={codeInputRef}
                    type='text'
                    name='code'
                    value={state.controls.code.value}
                    onChange={handleChange}
                    required
                    maxLength={100}
                    pattern='^[0-9a-zA-Z_]+$'
                />
            </FormGroup>

            <FormGroup
                controlId='flow_type'
                label='pim_apps.app.flow_type'
                info={<FlowTypeHelper />}
                required
                errors={Object.keys(state.controls.flow_type.errors)}
            >
                <FlowTypeSelect value={state.controls.flow_type.value as FlowType} onChange={handleFlowTypeSelect} />
            </FormGroup>
        </Form>
    );
};
