import {parseResponse} from './CategoryTreeFetcher';

const getCategoryResponse = (id: number, state: string) => ({
  attr: {id: `node_${id}`, 'data-code': `child-${id}`},
  data: `child ${id}`,
  state,
});

test('it parses a Category response', async () => {
  expect(parseResponse(getCategoryResponse(0, 'leaf'))).toStrictEqual({
    children: [],
    code: 'child-0',
    id: 0,
    label: 'child 0',
    readOnly: false,
    selectable: false,
    selected: false,
  });

  const responseWithChildren = {
    ...getCategoryResponse(1, ''),
    children: [getCategoryResponse(2, 'closed'), getCategoryResponse(3, 'unknown')],
  };

  expect(parseResponse(responseWithChildren)).toStrictEqual({
    children: [
      {
        children: undefined,
        code: 'child-2',
        id: 2,
        label: 'child 2',
        readOnly: false,
        selectable: false,
        selected: false,
      },
      {
        children: undefined,
        code: 'child-3',
        id: 3,
        label: 'child 3',
        readOnly: false,
        selectable: false,
        selected: false,
      },
    ],
    code: 'child-1',
    id: 1,
    label: 'child 1',
    readOnly: false,
    selectable: false,
    selected: false,
  });
});
