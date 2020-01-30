import React, {ChangeEvent, Dispatch, RefObject, useEffect, useReducer, useRef} from 'react';
import {useHistory} from 'react-router-dom';
import {ApplyButton, Form, FormGroup, FormInput, InlineHelper} from '../../common';
import {FlowType} from '../../model/flow-type.enum';
import {isErr} from '../../shared/fetch-result/result';
import {sanitize} from '../../shared/sanitize';
import {Translate} from '../../shared/translate';
import {connectionFetched} from '../actions/connections-actions';
import {
    codeGenerated,
    CreateFormAction,
    formIsInvalid,
    formIsValid,
    inputChanged,
    setError,
} from '../actions/create-form-actions';
import {useCreateConnection} from '../api-hooks/use-create-connection';
import {useConnectionsDispatch} from '../connections-context';
import {connectionFormReducer, CreateFormState} from '../reducers/connection-form-reducer';
import {FlowTypeHelper} from './FlowTypeHelper';
import {FlowTypeSelect} from './FlowTypeSelect';

const initialState: CreateFormState = {
    controls: {
        code: {name: 'code', value: '', errors: {}, dirty: false, valid: false},
        label: {name: 'label', value: '', errors: {}, dirty: false, valid: false},
        flow_type: {
            name: 'flow_type',
            value: FlowType.DATA_SOURCE,
            errors: {},
            dirty: false,
            valid: true,
        },
    },
    valid: false,
};

const useFormValidation = (
    state: CreateFormState,
    dispatch: Dispatch<CreateFormAction>,
    codeInputRef: RefObject<HTMLInputElement>,
    labelInputRef: RefObject<HTMLInputElement>
) => {
    useEffect(() => {
        [codeInputRef, labelInputRef].forEach(inputRef => {
            const input = inputRef.current;
            if (null === input) {
                return;
            }

            const name = input.name;
            if (
                false === input.checkValidity() &&
                0 === Object.keys(state.controls[name].errors).length &&
                true === state.controls[name].dirty
            ) {
                if (input.validity.valueMissing) {
                    dispatch(setError(name, `akeneo_connectivity.connection.connection.constraint.${name}.required`));
                }
                if (input.validity.patternMismatch) {
                    dispatch(setError(name, `akeneo_connectivity.connection.connection.constraint.${name}.invalid`));
                }
                if (input.validity.tooShort) {
                    dispatch(setError(name, `akeneo_connectivity.connection.connection.constraint.${name}.too_short`));
                }
            }
        });
    }, [dispatch, codeInputRef, labelInputRef, state.controls]);

    useEffect(() => {
        if (false === state.controls.label.valid || false === state.controls.code.valid) {
            dispatch(formIsInvalid());

            return;
        }
        dispatch(formIsValid());
    }, [dispatch, state.controls.label.valid, state.controls.code.valid]);
};

export const ConnectionCreateForm = () => {
    const history = useHistory();
    const connectionsDispatch = useConnectionsDispatch();

    const [state, dispatch] = useReducer(connectionFormReducer, initialState);
    const createConnection = useCreateConnection();

    const codeInputRef = useRef<HTMLInputElement>(null);
    const labelInputRef = useRef<HTMLInputElement>(null);
    useFormValidation(state, dispatch, codeInputRef, labelInputRef);

    useEffect(() => {
        if (true === state.controls.code.dirty) {
            return;
        }

        const value = sanitize(state.controls.label.value);
        if (state.controls.code.value === value) {
            return;
        }

        dispatch(codeGenerated(value));
    }, [dispatch, state.controls.label.value, state.controls.code.value, state.controls.code.dirty]);

    const handleSave = async () => {
        if (false === state.valid) {
            return;
        }

        const result = await createConnection({
            code: state.controls.code.value,
            label: state.controls.label.value,
            flow_type: state.controls.flow_type.value as FlowType,
        });
        if (isErr(result)) {
            result.error.errors.forEach(({name, reason}) => dispatch(setError(name, reason)));

            return;
        }

        connectionsDispatch(connectionFetched(result.value));

        history.push(`/connections/${state.controls.code.value}/edit`);
    };

    const handleChange = (event: ChangeEvent<HTMLInputElement>) => {
        dispatch(inputChanged(event.currentTarget.name, event.currentTarget.value));
    };

    const handleFlowTypeSelect = (flowType: FlowType) => dispatch(inputChanged('flow_type', flowType));

    return (
        <Form>
            <FormGroup
                controlId='label'
                label='akeneo_connectivity.connection.connection.label'
                errors={Object.keys(state.controls.label.errors)}
            >
                <FormInput
                    ref={labelInputRef}
                    type='text'
                    name='label'
                    value={state.controls.label.value}
                    onChange={handleChange}
                    required
                    minLength={3}
                    maxLength={100}
                />
            </FormGroup>

            <FormGroup
                controlId='code'
                label='akeneo_connectivity.connection.connection.code'
                errors={Object.keys(state.controls.code.errors)}
            >
                <FormInput
                    ref={codeInputRef}
                    type='text'
                    name='code'
                    value={state.controls.code.value}
                    onChange={handleChange}
                    required
                    minLength={3}
                    maxLength={100}
                    pattern='^[0-9a-zA-Z_]+$'
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
                required
                errors={Object.keys(state.controls.flow_type.errors)}
            >
                <FlowTypeSelect value={state.controls.flow_type.value as FlowType} onChange={handleFlowTypeSelect} />
            </FormGroup>

            <ApplyButton onClick={handleSave} disabled={false === state.valid}>
                <Translate id='pim_common.save' />
            </ApplyButton>
        </Form>
    );
};
