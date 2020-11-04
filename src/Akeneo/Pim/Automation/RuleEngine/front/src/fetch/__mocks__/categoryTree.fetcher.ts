const fetchRootCategoryTrees = jest.fn(() => [
  {code: 'master', id: 1, labels: {en_US: 'master'}, parent: null},
  {code: 'sales', id: 200, labels: {en_US: 'sales'}, parent: null},
  {code: 'marketing', id: 300, labels: {en_US: 'marketing'}, parent: null},
]);

const fetchCategoryTree = jest.fn(() => ({
  ok: true,
  json: jest.fn(() => [
    {
      attr: {id: 'node_1', 'data-code': 'master'},
      children: [],
      data: 'Master catalog',
      selectedChildrenCount: 2,
      state: 'open jstree-root',
    },
  ]),
}));

const fetchCategoryTreeChildren = jest.fn(() => ({
  ok: true,
  json: jest.fn(() => ({
    children: [],
  })),
}));

export {fetchCategoryTree, fetchCategoryTreeChildren, fetchRootCategoryTrees};
