import * as React from 'react';
import {create} from 'react-test-renderer';
import {Breadcrumb, BreadcrumbItem} from '../../../../application/common';

describe('Button component', () => {
    test('Matches the snapshot', () => {
        const component = create(
            <Breadcrumb>
                <BreadcrumbItem label='Is a link' onClick={() => undefined} />
                <BreadcrumbItem label='Not a link' />
                <BreadcrumbItem label='Is a link and last item' onClick={() => undefined} />
            </Breadcrumb>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
