import * as React from 'react';
import {create} from 'react-test-renderer';
import {PageHeader} from '@src/common';

describe('PageHeader', () => {
    it('should render', () => {
        const component = create(
            <PageHeader
                breadcrumb={<>breadcrumb</>}
                buttons={[<>button 1</>, <>button 2</>]}
                userButtons={<>user buttons</>}
            >
                title
            </PageHeader>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
