import React from 'react';
import {render} from '../../storybook/test-util';
import {Helper} from "./Helper";

describe('A helper', () => {
    it('renders a big helper', () => {
        const helperTitle = 'Helper title';
        const helperMessage = 'A Helper message';

        const {getByText} = render(<Helper type='big' level='info' title={helperTitle}>{helperMessage}</Helper>);

        expect(getByText(helperTitle)).toBeInTheDocument();
        expect(getByText(helperMessage)).toBeInTheDocument();
    });

    it('cannot be instantiated if a big helper does not have a title', () => {
        expect(() => {
            render(<Helper type='big' level='info'>a message</Helper>);
        }).toThrow('A big helper should have a title. None given.');
    });

    it('renders a small helper', () => {
        const helperMessage = 'A Helper message';

        const {getByText} = render(<Helper type='small' level='info'>{helperMessage}</Helper>);

        expect(getByText(helperMessage)).toBeInTheDocument();
    });

    it('renders an inline helper', () => {
        const helperMessage = 'A Helper message';

        const {getByText} = render(<Helper type='inline' level='info'>{helperMessage}</Helper>);

        expect(getByText(helperMessage)).toBeInTheDocument();
    });

    test.each(['small', 'inline'])
    ('cannot be instantiated if a(n) %s helper has a title', (type: any) => {
        expect(() => {
            render(<Helper type={type} title='Some title' level='info'>a message</Helper>);
        }).toThrow('A small or inline helper cannot have title.');
    })
});
