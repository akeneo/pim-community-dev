import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {fireEvent, getByTestId} from '@testing-library/react';
import {act} from 'react-dom/test-utils';
import useDebounce from 'akeneoassetmanager/platform/hook/use-debounce';

let container;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});
afterEach(() => {
  document.body.removeChild(container);
  container = null;
});
jest.useFakeTimers();

const App = () => {
  const [value, setValue] = React.useState(0);
  const debouncedValue = useDebounce(value, 5000);
  const handleClick = () => setValue(state => state + 1);

  return (
    <>
      <div data-testid="value">{debouncedValue}</div>
      <button data-testid="button" onClick={handleClick} />
    </>
  );
};

test('It loads with the expected initial state', async () => {
  ReactDOM.render(<App />, container);
  const value = getByTestId(container, 'value');

  expect(value.textContent).toBe('0');
});

test('It display the correct value only after the delay', async () => {
  ReactDOM.render(<App />, container);
  const value = getByTestId(container, 'value');
  const button = getByTestId(container, 'button');

  expect(value.textContent).toBe('0');
  await act(async () => {
    await fireEvent.click(button);
    await fireEvent.click(button);
    await fireEvent.click(button);
  });
  expect(value.textContent).toBe('0');
  act(() => {
    jest.runAllTimers();
  });
  expect(value.textContent).toBe('3');
});
