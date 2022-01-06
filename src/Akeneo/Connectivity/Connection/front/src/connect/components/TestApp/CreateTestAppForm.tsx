import React from 'react';
import {TextInput} from 'akeneo-design-system';
import {useFormik} from 'formik';

import {Translate, useTranslate} from '../../../shared/translate';

export const CreateTestAppForm = () => {
    const translate = useTranslate();

    const formik = useFormik({
        initialValues: {
            name: '',
            activate_url: '',
            callback_url: '',
        },
        onSubmit: (values) => {
            console.log(values);
        }
    });

    return (
        <form onSubmit={formik.handleSubmit}>
            <label htmlFor="name">
                <Translate id='akeneo_connectivity.connection.connect.marketplace.test_app.modal.fields.name' />
                &nbsp;
                <Translate id='pim_common.required_label' />
            </label>
            <TextInput
                id='name'
                name='name'
                type='text'
                value={formik.values.name}
                onChange={formik.handleChange}
            />
        </form>
    );
};
