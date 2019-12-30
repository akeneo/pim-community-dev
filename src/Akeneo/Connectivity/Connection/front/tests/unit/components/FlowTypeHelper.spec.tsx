import * as React from 'react';
import {create} from 'react-test-renderer';
import {FlowTypeHelper} from '../../../src/apps/components/FlowTypeHelper';

describe('FlowTypeHelper', () => {
    it('should render', () => {
        const component = create(<FlowTypeHelper />);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
