import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, screen} from '@testing-library/react';
import EventLogDatetime from '@src/webhook/components/EventLogDatetime';

describe('it displays datetime according to a timestamp in second', () => {
    test('the datetime is display with good formatting', () => {
        render(<EventLogDatetime timestamp={1615994468000} />);

        expect(screen.getByText('03/17/2021', {exact: false})).toBeInTheDocument();
        expect(screen.getByText('03:21:08 PM', {exact: false})).toBeInTheDocument();
    });
});
