import {isSameAsDefaultFiltersValues} from '@src/webhook/model/EventSubscriptionLogFilters';
import {EventSubscriptionLogLevel} from '@src/webhook/model/EventSubscriptionLogLevel';

test('it compares successfully the default filters values', () => {
    expect(
        isSameAsDefaultFiltersValues({
            levels: [
                EventSubscriptionLogLevel.INFO,
                EventSubscriptionLogLevel.NOTICE,
                EventSubscriptionLogLevel.WARNING,
                EventSubscriptionLogLevel.ERROR,
            ],
            text: '',
            dateTimeStart: undefined,
            dateTimeEnd: undefined,
        })
    ).toBeTruthy();
});

test('it fails to compare modified filters values with the default ones', () => {
    expect(
        isSameAsDefaultFiltersValues({
            levels: [EventSubscriptionLogLevel.INFO],
            text: '',
            dateTimeStart: undefined,
            dateTimeEnd: undefined,
        })
    ).toBeFalsy();

    expect(
        isSameAsDefaultFiltersValues({
            levels: [
                EventSubscriptionLogLevel.INFO,
                EventSubscriptionLogLevel.NOTICE,
                EventSubscriptionLogLevel.WARNING,
                EventSubscriptionLogLevel.ERROR,
            ],
            text: 'search value',
            dateTimeStart: undefined,
            dateTimeEnd: undefined,
        })
    ).toBeFalsy();
});
