import ReactDOM from 'react-dom';
import React, {FC, useRef} from 'react';
import {fireEvent, act, getByTestId} from '@testing-library/react';
import {useInfiniteScroll} from '../../../../src/hooks/useInfiniteScroll';

const MockComponent: FC = ({fetch}) => {
  const testRef = useRef(null);
  const [fetchResponse, updateFetchResponse] = useInfiniteScroll(fetch, testRef.current);

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
  const fetch = jest.fn(search => Promise.resolve<any[]>([]));
  fetch.mockResolvedValueOnce(items);

  await act(async () => ReactDOM.render(<MockComponent fetch={fetch} />, container as HTMLElement));

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
  const fetch = jest.fn(search => Promise.resolve<any[]>([]));
  fetch.mockResolvedValueOnce(items).mockResolvedValueOnce(nextItems);

  await act(async () => ReactDOM.render(<MockComponent fetch={fetch} />, container as HTMLElement));

  await act(async () => {
    fireEvent.scroll(getByTestId(container, 'test-component'), {target: {scrollY: 100}});
  });

  expect(container.querySelectorAll('ul li').length).toEqual(4);
});

it('it fetches items until there is no items in most recent response', async () => {
  const firstPageItems = [
    {
      title: 'title 1',
    },
    {
      title: 'title 2',
    },
  ];
  const secondPageItems = [
    {
      title: 'title 3',
    },
  ];
  const thirPageItems: any[] = [];

  const fetch = jest.fn(search => Promise.resolve<any[]>([]));
  fetch
    .mockResolvedValueOnce(firstPageItems)
    .mockResolvedValueOnce(secondPageItems)
    .mockResolvedValueOnce(thirPageItems);

  await act(async () => ReactDOM.render(<MockComponent fetch={fetch} />, container as HTMLElement));

  await act(async () => {
    fireEvent.scroll(getByTestId(container, 'test-component'), {target: {scrollY: 100}});
  });

  await act(async () => {
    fireEvent.scroll(getByTestId(container, 'test-component'), {target: {scrollY: 100}});
  });

  await act(async () => {
    fireEvent.scroll(getByTestId(container, 'test-component'), {target: {scrollY: 100}});
  });

  expect(fetch).toBeCalledTimes(3);
});

it('it can handle error during the fetch', async () => {
  const fetch = search => {
    throw new Error('Async error');
  };
  await act(async () => ReactDOM.render(<MockComponent fetch={fetch} />, container as HTMLElement));

  expect(container.innerHTML).toStrictEqual('error');
});
