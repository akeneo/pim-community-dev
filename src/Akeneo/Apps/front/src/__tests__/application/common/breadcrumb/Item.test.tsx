import * as React from 'react';
import {create} from 'react-test-renderer';
import {BreadcrumbItem} from '../../../../application/common';

describe('Breadcrumb Item component', () => {
    test('Matches the snapshot', () => {
        const component = create(<BreadcrumbItem label='Is a link' onClick={() => undefined} isLast={true} />);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
