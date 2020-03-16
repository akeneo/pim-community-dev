import {Form, FormGroup, FormInput} from '@src/common';
import React from 'react';
import {createWithProviders} from '../../../../test-utils';

describe('Form', () => {
    it('should render', () => {
        const component = createWithProviders(
            <Form>
                <FormGroup
                    controlId='control-id'
                    label='label'
                    required
                    helper='helper'
                    errors={['error-1', 'error-2']}
                >
                    <FormInput type='text' value='value' />
                </FormGroup>
            </Form>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
