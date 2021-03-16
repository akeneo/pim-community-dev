import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {EventLogBadge} from '@src/webhook/components/EventLogBadge';
import {render, screen} from 'akeneo-design-system/lib/storybook/test-util';
import {EventSubscriptionLogLevel} from '@src/webhook/model/EventSubscriptionLogLevel';

test.each([
    [EventSubscriptionLogLevel.INFO, 'INFO', 'rgb(61, 107, 69)'],
    [EventSubscriptionLogLevel.NOTICE, 'NOTICE', 'rgb(17, 50, 77)'],
    [EventSubscriptionLogLevel.WARNING, 'WARNING', 'rgb(149, 108, 37)'],
    [EventSubscriptionLogLevel.ERROR, 'ERROR', 'rgb(127, 57, 47)'],
])('It render the badge with the level %i', (level, label, color) => {
    render(<EventLogBadge level={level}>{label}</EventLogBadge>);

    const badge = screen.getByText(label);

    expect(badge).toBeInTheDocument();
    expect(getComputedStyle(badge).color).toEqual(color);
});
