import {useCallback} from 'react';
import {debounceCallback} from './debounce-callback';

/**
 * Grouping multiple calls to a function (like AJAX request) into a single one
 * @param callback
 * @param delay
 */
const useDebounceCallback = (callback: (...args: any) => any, delay: number) => {
    return useCallback(debounceCallback(callback, delay), [callback, delay]);
};

export {useDebounceCallback};
