import * as React from 'react';
import {create} from 'react-test-renderer';
import {Section} from '../../../../src/application/common';

describe('Section', () => {
    it('should render', () => {
        const component = create(<Section title={'title'} />);

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should render content', () => {
        const component = create(<Section title={'title'}>content</Section>);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
