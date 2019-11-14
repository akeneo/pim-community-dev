import React, {ChangeEvent, useRef, useEffect, useContext, useReducer} from 'react';
import {FlowType} from '../../../domain/apps/flow-type.enum';
import {ApplyButton, Form, FormGroup, FormInput} from '../../common';
import {FlowTypeHelper} from './FlowTypeHelper';
import {FlowTypeSelect} from './FlowTypeSelect';
import {appFormReducer, CreateFormState} from '../reducers/app-form-reducer';
import {inputChanged, setError, setValidated} from '../actions/create-form-actions';
import {sanitize} from '../../shared/sanitize';
import {Translate, TranslateContext} from '../../shared/translate';
import {fetch} from '../../shared/fetch';
import {isErr} from '../../shared/fetch/result';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {useRoute} from '../../shared/router';
import {useHistory} from 'react-router';

const initialState: CreateFormState = {
    controls: {
        code: {name: 'code', value: '', errors: {}, dirty: false, valid: false, validated: true},
        label: {name: 'label', value: '', errors: {}, dirty: false, valid: false, validated: true},
        flow_type: {
            name: 'flow_type',
            value: FlowType.DATA_SOURCE,
            errors: {},
            dirty: false,
            valid: true,
            validated: true,
        },
    },
    valid: false,
};

interface ResultError {
    message: string;
    errors: Array<{
        name: string;
        reason: string;
    }>;
}

export const CreateAppForm = () => {
    const [state, dispatch] = useReducer(appFormReducer, initialState);

    const codeInputRef = useRef<HTMLInputElement>(null);
    const labelInputRef = useRef<HTMLInputElement>(null);

    const url = useRoute('akeneo_apps_create_rest');
    const notify = useNotify();
    const translate = useContext(TranslateContext);
    const history = useHistory();

    useEffect(() => {
        [codeInputRef, labelInputRef].forEach(inputRef => {
            const input = inputRef.current;
            if (null === input) {
                return;
            }

            const name = input.name;
            // if (true === state.controls[name].validated) {
            //     return;
            // }

            if (false === input.checkValidity()) {
                if (input.validity.valueMissing) {
                    dispatch(setError(name, `akeneo_apps.constraint.${name}.required`));
                }
                if (input.validity.patternMismatch) {
                    dispatch(setError(name, `akeneo_apps.constraint.${name}.invalid`));
                }
            }
            // dispatch(setValidated(name));
        });
    }, [state.controls.code.value, state.controls.label.value, dispatch]);

    useEffect(() => {
        if (true === state.controls.code.dirty) {
            return;
        }

        const value = sanitize(state.controls.label.value);
        if (state.controls.code.value === value) {
            return;
        }

        dispatch(inputChanged('code', value, false));
    }, [state.controls.label.value, dispatch]);

    const handleSave = async () => {
        if (false === state.valid) {
            return;
        }
        const data = {
            code: state.controls.code.value,
            label: state.controls.label.value,
            flow_type: state.controls.flow_type.value,
        };

        const result = await fetch<undefined, ResultError>(url, {
            method: 'POST',
            headers: [['Content-type', 'application/json']],
            body: JSON.stringify(data),
        });

        if (isErr(result)) {
            if (undefined === result.error.errors) {
                notify(NotificationLevel.ERROR, translate('pim_apps.create_app.flash.error'));
                return;
            }
            result.error.errors.forEach(({name, reason}) => dispatch(setError(name, reason)));
            return;
        }

        notify(NotificationLevel.SUCCESS, translate('pim_apps.create_app.flash.success'));
        history.push(`/apps/${data.code}/edit`);
    };

    const handleChange = (event: ChangeEvent<HTMLInputElement>) => {
        dispatch(inputChanged(event.currentTarget.name, event.currentTarget.value));
    };

    const handleFlowTypeSelect = (flowType: FlowType) => dispatch(inputChanged('flow_type', flowType));

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

            <ApplyButton onClick={handleSave} disabled={false === state.valid}>
                <Translate id='pim_common.save' />
            </ApplyButton>
        </Form>
    );
};
