import {Modal} from '@src/common';
import React from 'react';
import {createWithProviders} from '../../../test-utils';

describe('Modal', () => {
    it('should render', () => {
        const component = createWithProviders(
            <Modal title='title' subTitle='subtitle' description='description' onCancel={() => undefined}>
                content
            </Modal>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
