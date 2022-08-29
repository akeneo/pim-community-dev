jest.unmock('./useNumberValue');

import {act, renderHook} from '@testing-library/react-hooks';
import {useNumberValue} from './useNumberValue';

type InitialValue = string | number | null;
type InputValue = string;
type ExpectedInputValue = string;
type ExpectedResult = string | number;

const initialValues: [InitialValue, ExpectedInputValue][] = [
    [null, ''],
    [42, '42'],
    ['', ''],
    ['42', '42'],
];

test.each(initialValues)('it returns the initial value as string from "%s"', (initialValue, expectedValue) => {
    const {result} = renderHook(() => useNumberValue(initialValue, jest.fn()));

    expect(result.current[0]).toEqual(expectedValue);
});

const values: [InputValue, ExpectedInputValue, ExpectedResult][] = [
    ['', '', ''],
    ['4', '4', 4],
    ['a', '', ''],
    ['.', '', ''],
    ['4.', '4.', 4],
    ['4..', '4.', 4],
    ['4.0', '4.0', 4],
    ['4.2', '4.2', 4.2],
];

test.each(values)('it filters invalid characters in initial value "%s"', (value, expectedValue) => {
    const {result} = renderHook(() => useNumberValue(value, jest.fn()));

    expect(result.current[0]).toEqual(expectedValue);
});

test.each(values)(
    'it filters and dispath the new value when typing "%s"',
    (value, expectedInputValue, expectedResult) => {
        const onChange = jest.fn();
        const {result} = renderHook(() => useNumberValue('', onChange));

        act(() => result.current[1](value));

        expect(result.current[0]).toEqual(expectedInputValue);
        expect(onChange).toHaveBeenCalledWith(expectedResult);
    }
);

test('it updates when the initial value change externally', () => {
    const onChange = jest.fn();

    const {result, rerender} = renderHook(({value, onChange}) => useNumberValue(value, onChange), {
        initialProps: {
            value: '42',
            onChange: onChange,
        },
    });

    expect(result.current[0]).toEqual('42');
    rerender({value: '65', onChange: onChange});
    expect(result.current[0]).toEqual('65');
    expect(onChange).not.toHaveBeenCalled();
});
