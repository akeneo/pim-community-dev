import React, {useCallback, useReducer, useContext} from 'react';
import {useHistory} from 'react-router';
import {FlowType} from '../../../domain/apps/flow-type.enum';
import {ApplyButton, Modal} from '../../common';
import {fetch} from '../../shared/fetch';
import {isErr} from '../../shared/fetch/result';
import {useRoute} from '../../shared/router';
import {Translate} from '../../shared/translate';
import {AppForm} from '../components/AppForm';
import {appFormReducer, FormState} from '../reducers/app-form-reducer';
import {NotificationLevel, useNotify} from '../../shared/notify';
import {TranslateContext} from '../../shared/translate';

interface ResultError {
    message: string;
    errors: Array<{
        name: string;
        reason: string;
    }>;
}

const initialState: FormState = {
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

export const AppCreate = () => {
    const history = useHistory();
    const url = useRoute('akeneo_apps_create_rest');
    const notify = useNotify();
    const translate = useContext(TranslateContext);

    const [state, dispatch] = useReducer(appFormReducer, initialState);

    const handleSave = useCallback(() => {
        if (false === state.valid) {
            return;
        }
        const data = {
            code: state.controls.code.value,
            label: state.controls.label.value,
            flow_type: state.controls.flow_type.value,
        };

        fetch<undefined, ResultError>(url, {
            method: 'POST',
            headers: [['Content-type', 'application/json']],
            body: JSON.stringify(data),
        }).then(result => {
            if (isErr(result)) {
                if (undefined === result.error.errors) {
                    notify(NotificationLevel.ERROR, translate('pim_apps.create_app.flash.error'));
                    return;
                }
                result.error.errors.forEach(({name, reason}) => dispatch({type: 'ERROR', name, code: reason}));
                return;
            }

            notify(NotificationLevel.SUCCESS, translate('pim_apps.create_app.flash.success'));
            history.push(`/apps/${data.code}/edit`);
        });
    }, [url, state, history, notify, translate]);

    return (
        <Modal
            subTitle={<Translate id='pim_apps.apps' />}
            title={<Translate id='pim_apps.create_app.title' />}
            description={<Translate id='pim_apps.create_app.description' />}
            onCancel={() => history.push('/apps')}
            buttons={
                <ApplyButton onClick={handleSave} disabled={false === state.valid}>
                    <Translate id='pim_common.save' />
                </ApplyButton>
            }
        >
            <AppForm state={state} dispatch={dispatch} />
        </Modal>
    );
};
