import * as React from 'react';
import {create} from 'react-test-renderer';
import {FlowTypeSelect} from '../../../../../src/application/apps/components/FlowTypeSelect';
import {FlowType} from '../../../../../src/domain/apps/flow-type.enum';

describe('FlowTypeSelect', () => {
    it('should render', () => {
        const component = create(<FlowTypeSelect value={FlowType.DATA_SOURCE} onChange={() => undefined} />);

        expect(component.toJSON()).toMatchSnapshot();
    });
});
