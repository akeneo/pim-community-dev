import * as React from 'react';
import {create} from 'react-test-renderer';
import {BreadcrumbItem} from '../../../../application/common';

describe('Breadcrumb Item component', () => {
    test('Matches the snapshot', () => {
        const component = create(
            <BreadcrumbItem onClick={() => undefined} isLast={true}>
                Is clickable and the last item
            </BreadcrumbItem>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
