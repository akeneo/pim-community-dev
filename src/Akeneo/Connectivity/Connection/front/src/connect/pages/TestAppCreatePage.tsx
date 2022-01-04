import React, {ChangeEvent, Dispatch, RefObject, useCallback, useEffect, useReducer, useRef} from 'react';
import {useHistory} from 'react-router';
import {Modal, AppIllustration, Button, getColor, getFontSize} from 'akeneo-design-system';
import {Form, FormGroup, FormInput} from '../../common';
import styled from '../../common/styled-with-theme';
import {Translate, useTranslate} from '../../shared/translate';
import {useRouter} from '../../shared/router/use-router';
import {connectionFormReducer, CreateFormState} from '../../settings/reducers/connection-form-reducer';
import {
    codeGenerated,
    CreateFormAction,
    formIsInvalid,
    formIsValid,
    inputChanged,
    setError
} from '../../settings/actions/create-form-actions';
import {sanitize} from '../../shared/sanitize';

const Subtitle = styled.h3`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('default')};
    text-transform: uppercase;
    font-weight: normal;
    margin: 0 0 6px 0;
`;

const Title = styled.h2`
    color: ${getColor('grey', 140)};
    font-size: 28px;
    font-weight: normal;
    line-height: 28px;
    margin: 0;
`;

const Helper = styled.div`
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

const initialState: CreateFormState = {
    controls: {
        name: {name: 'name', value: '', errors: {}, dirty: false, valid: false},
        activate_url: {name: 'activate_url', value: '', errors: {}, dirty: false, valid: false},
        callback_url: {name: 'callback_url', value: '', errors: {}, dirty: false, valid: false},
    },
    valid: false,
};

const useFormValidation = (
    state: CreateFormState,
    dispatch: Dispatch<CreateFormAction>,
    nameInputRef: RefObject<HTMLInputElement>,
    activateUrlInputRef: RefObject<HTMLInputElement>,
    callbackUrlInputRef: RefObject<HTMLInputElement>
) => {
    useEffect(() => {
        [nameInputRef, activateUrlInputRef, callbackUrlInputRef].forEach(inputRef => {
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
                const translationKey = 'akeneo_connectivity.connection.connect.marketplace.test_app.modal.constraint';
                if (input.validity.valueMissing) {
                    dispatch(setError(name, `${translationKey}.${name}.required`));
                }
                if (input.validity.patternMismatch) {
                    dispatch(setError(name, `${translationKey}.${name}.invalid`));
                }
                if (input.validity.tooLong) {
                    dispatch(setError(name, `${translationKey}.${name}.too_long`));
                }
            }
        });
    }, [dispatch, nameInputRef, activateUrlInputRef, callbackUrlInputRef, state.controls]);

    useEffect(() => {
        if (!state.controls.name.valid || !state.controls.activate_url.valid || !state.controls.callback_url.valid) {
            dispatch(formIsInvalid());

            return;
        }
        dispatch(formIsValid());
    }, [dispatch, state.controls.name.valid, state.controls.activate_url.valid, state.controls.callback_url.valid]);
};

export const TestAppCreatePage = () => {
    const history = useHistory();
    const generateUrl = useRouter();
    const translate = useTranslate();

    const [state, dispatch] = useReducer(connectionFormReducer, initialState);

    const nameInputRef = useRef<HTMLInputElement>(null);
    const activateUrlInputRef = useRef<HTMLInputElement>(null);
    const callbackUrlInputRef = useRef<HTMLInputElement>(null);

    useFormValidation(state, dispatch, nameInputRef, activateUrlInputRef, callbackUrlInputRef);

    const fields = [
        {
            name: 'name',
            element: nameInputRef
        }, {
            name: 'activate_url',
            element: activateUrlInputRef
        }, {
            name: 'callback_url',
            element: callbackUrlInputRef
        }];

    // TODO : handle the click
    const handleCreate = () => null;

    const handleCancel = useCallback(() => {
        history.push(
            generateUrl('akeneo_connectivity_connection_connect_marketplace')
        );
    }, [history, generateUrl]);

    const handleChange = (event: ChangeEvent<HTMLInputElement>) => {
        dispatch(inputChanged(event.currentTarget.name, event.currentTarget.value));
    };

    return (
        <Modal onClose={handleCancel} illustration={<AppIllustration />} closeTitle={translate('pim_common.cancel')}>
            <Subtitle>{translate('akeneo_connectivity.connection.connect.marketplace.test_app.modal.subtitle')}</Subtitle>
            <Title>{translate('akeneo_connectivity.connection.connect.marketplace.test_app.modal.title')}</Title>
            <Helper>
                <p>
                    {translate('akeneo_connectivity.connection.connect.marketplace.test_app.modal.description')}
                    <span className="cline-any cline-neutral">&nbsp;</span>
                    <Link href={'https://help.akeneo.com/pim/articles/manage-your-apps.html#create-a-test-app'}>
                        {translate('akeneo_connectivity.connection.connect.marketplace.test_app.modal.link')}
                    </Link>
                </p>
            </Helper>
            <Form>
                {
                    fields.map((field, key) => {
                    return (<FormGroup
                        key={key}
                        controlId={field.name}
                        label={`akeneo_connectivity.connection.connect.marketplace.test_app.modal.fields.${field.name}`}
                        helpers={Object.keys(state.controls[field.name].errors).map((error, i) => (
                            <Helper inline level='error' key={i}>
                                <Translate id={error} />
                            </Helper>
                        ))}
                    >
                        <FormInput
                            ref={field.element}
                            type='text'
                            name={field.name}
                            value={state.controls[field.name].value}
                            onChange={handleChange}
                            required
                            maxLength={250}
                        />
                    </FormGroup>);
                })}
            </Form>
            <Modal.BottomButtons>
                <Button onClick={handleCancel} level='tertiary'>
                    {translate('pim_common.cancel')}
                </Button>
                <Button onClick={handleCreate} disabled={false === state.valid} level='primary'>
                    {translate('pim_common.create')}
                </Button>
            </Modal.BottomButtons>
        </Modal>
    );
};
