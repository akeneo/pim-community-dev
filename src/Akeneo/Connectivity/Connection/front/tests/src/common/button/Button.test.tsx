import * as React from 'react';
import {create} from 'react-test-renderer';
import {Button} from '@src/common/components/button/Button';

describe('Button', () => {
    it('should render', () => {
        const component = create(<Button onClick={() => undefined}>content</Button>);

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should be disabled', () => {
        const component = create(
            <Button onClick={() => undefined} disabled>
                content
            </Button>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should display a counter', () => {
        const component = create(
            <Button onClick={() => undefined} count={9}>
                content
            </Button>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });

    it('should have custom classes', () => {
        const component = create(
            <Button onClick={() => undefined} classNames={['class1', 'class2']}>
                content
            </Button>
        );

        expect(component.toJSON()).toMatchSnapshot();
    });
});
