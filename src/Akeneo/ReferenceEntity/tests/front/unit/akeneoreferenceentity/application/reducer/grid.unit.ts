import reducer, {createState, createQuery, NormalizedFilter} from 'akeneoreferenceentity/application/reducer/grid';

describe('akeneo > reference entity > application > reducer --- grid', () => {
  test('I can initialize an empty state', () => {
    const newState = reducer(undefined, {
      type: 'GO_FIRST_PAGE',
    });

    expect(newState).toEqual({
      query: {
        page: 0,
        columns: [],
        filters: [],
        limit: 25,
      },
      items: [],
      total: 0,
      isFetching: false,
    });
  });

  test('I ignore other actions', () => {
    const originalState = {my: 'originalState'};

    const newState = reducer(originalState, {
      type: 'ANOTHER_ACTION',
    });

    expect(newState).toBe(originalState);
  });

  test('I can receive original data', () => {
    const state = createState({
      query: {},
      items: [],
      total: 0,
      isFetching: false,
    });

    const newState = reducer(state, {
      type: 'DATA_RECEIVED',
      append: false,
      data: {items: ['first_item', 'second_item']},
      total: 10,
    });

    expect(newState).toEqual({
      query: {},
      items: ['first_item', 'second_item'],
      total: 10,
      isFetching: false,
    });
  });

  test('I can receive appended data', () => {
    const state = createState({
      query: {},
      items: ['first_item', 'second_item'],
      total: 0,
      isFetching: false,
    });
    const newState = reducer(state, {
      type: 'DATA_RECEIVED',
      append: true,
      data: {items: ['third_item']},
      total: 10,
    });

    expect(newState).toEqual({
      query: {},
      items: ['first_item', 'second_item', 'third_item'],
      total: 10,
      isFetching: false,
    });
  });

  test('I can start loadind result', () => {
    const state = createState({
      query: {},
      items: [],
      total: 0,
      isFetching: false,
    });
    const newState = reducer(state, {
      type: 'START_LOADING_RESULTS',
    });

    expect(newState).toEqual({
      query: {},
      items: [],
      total: 0,
      isFetching: true,
    });
  });

  test('I can stop loadind result', () => {
    const state = createState({
      query: {},
      items: [],
      total: 0,
      isFetching: true,
    });
    const newState = reducer(state, {
      type: 'STOP_LOADING_RESULTS',
    });

    expect(newState).toEqual({
      query: {},
      items: [],
      total: 0,
      isFetching: false,
    });
  });

  test('I can go to the next page', () => {
    const state = createState({
      query: {
        page: 0,
      },
      items: [],
      total: 0,
      isFetching: false,
    });
    const newState = reducer(state, {
      type: 'GO_NEXT_PAGE',
    });

    expect(newState).toEqual({
      query: {
        page: 1,
      },
      items: [],
      total: 0,
      isFetching: false,
    });
  });

  test('I can go to the first page', () => {
    const state = createState({
      query: {
        page: 10,
      },
      items: [],
      total: 0,
      isFetching: false,
    });
    const newState = reducer(state, {
      type: 'GO_FIRST_PAGE',
    });

    expect(newState).toEqual({
      query: {
        page: 0,
      },
      items: [],
      total: 0,
      isFetching: false,
    });
  });

  test('I can normalize a filter', () => {
    const normalizer = NormalizedFilter.create({field: 'name', operator: '=', value: 'michel'});

    expect(normalizer.field).toEqual('name');
    expect(normalizer.operator).toEqual('=');
    expect(normalizer.value).toEqual('michel');
  });

  test('I cannot normalize a malformed filter', () => {
    expect(() => NormalizedFilter.create({field: 'name', operator: '='})).toThrow();
    expect(() => NormalizedFilter.create({field: 'name', value: 'michel'})).toThrow();
    expect(() => NormalizedFilter.create({operator: '=', value: 'michel'})).toThrow();
  });

  test('I can create a query', () => {
    expect(createQuery({})).toEqual({
      columns: [],
      filters: [],
      limit: 25,
      page: 0,
    });

    expect(
      createQuery({
        columns: ['my_column'],
        filters: ['my_filter'],
        limit: 50,
        page: 2,
      })
    ).toEqual({
      columns: ['my_column'],
      filters: ['my_filter'],
      limit: 50,
      page: 2,
    });
  });
});
