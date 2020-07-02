import ReactDOM from 'react-dom';
import React, {FC, useRef} from 'react';
import {fireEvent, act, getByTestId} from '@testing-library/react';
import {useInfiniteScroll} from '../../../../src/hooks/useInfiniteScroll';

const MockComponent: FC = ({fetch, limit}) => {
  const testRef = useRef(null);
  const fetchResponse = useInfiniteScroll(fetch, testRef.current, limit);

  if (fetchResponse.hasError) {
    return 'error';
  }

  return (
    <ul data-testid="test-component" ref={testRef}>
      {fetchResponse.items.map(item => (
        <li key={item.title}>item.title</li>
      ))}
    </ul>
  );
};

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});
afterEach(() => {
  document.body.removeChild(container);
  container = null;
});
it('it can fetch the first items', async () => {
  const items = [
    {
      title: 'title 1',
    },
    {
      title: 'title 2',
    },
  ];
  const fetch = jest.fn((search, limit) => Promise.resolve<any[]>([]));
  fetch.mockResolvedValueOnce(items);

  await act(async () => ReactDOM.render(<MockComponent fetch={fetch} limit={2} />, container as HTMLElement));

  expect(container.querySelectorAll('ul li').length).toEqual(2);
});

it('it can fetch the next items', async () => {
  const items = [
    {
      title: 'title 1',
    },
    {
      title: 'title 2',
    },
  ];
  const nextItems = [
    {
      title: 'title 3',
    },
    {
      title: 'title 4',
    },
  ];
  const fetch = jest.fn((search, limit) => Promise.resolve<any[]>([]));
  fetch.mockResolvedValueOnce(items).mockResolvedValueOnce(nextItems);

  await act(async () => ReactDOM.render(<MockComponent fetch={fetch} limit={2} />, container as HTMLElement));

  await act(async () => {
    fireEvent.scroll(getByTestId(container, 'test-component'), {target: {scrollY: 100}});
  });

  expect(container.querySelectorAll('ul li').length).toEqual(4);
});

it('it does not fetch more items after the last one (aka the last fetch return less items than the limit)', async () => {
  const items = [
    {
      title: 'title 1',
    },
    {
      title: 'title 2',
    },
  ];
  const nextItems = [
    {
      title: 'title 3',
    },
  ];

  const fetch = jest.fn((search, limit) => Promise.resolve<any[]>([]));
  fetch.mockResolvedValueOnce(items).mockResolvedValueOnce(nextItems);

  await act(async () => ReactDOM.render(<MockComponent fetch={fetch} limit={2} />, container as HTMLElement));

  await act(async () => {
    fireEvent.scroll(getByTestId(container, 'test-component'), {target: {scrollY: 100}});
  });

  await act(async () => {
    fireEvent.scroll(getByTestId(container, 'test-component'), {target: {scrollY: 100}});
  });

  expect(fetch).toBeCalledTimes(2);
});

it('it can handle error during the fetch', async () => {
  const fetch = jest.fn((search, limit) => Promise.reject<string>());
  fetch.mockRejectedValueOnce(new Error('Async error'));

  await act(async () => ReactDOM.render(<MockComponent fetch={fetch} limit={2} />, container as HTMLElement));

  expect(container.innerHTML).toStrictEqual('error');
});
