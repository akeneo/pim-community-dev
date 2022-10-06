// jest-dom adds custom jest matchers for asserting on DOM nodes.
// allows you to do things like:
// expect(element).toHaveTextContent(/react/i)
// learn more: https://github.com/testing-library/jest-dom
import '@testing-library/jest-dom/extend-expect';

beforeEach(() => {
    //Necessary to avoid annoying console.error() dumped in test output, even when they are expected
    // Cf this issue : https://github.com/facebook/jest/issues/5785
    jest.spyOn(console, 'error').mockImplementation(() => {});

    window.HTMLElement.prototype.scrollTo = jest.fn();

    const intersectionObserverMock = () => ({
        observe: jest.fn(),
        unobserve: jest.fn(),
    });

    window.IntersectionObserver = jest.fn().mockImplementation(intersectionObserverMock);
});

jest.mock('react-intl', () => ({
    ...jest.requireActual('react-intl'),
    useIntl: () => ({
        formatMessage: ({id, defaultMessage}: {id: string; defaultMessage: string}) => defaultMessage,
    }),
}));
