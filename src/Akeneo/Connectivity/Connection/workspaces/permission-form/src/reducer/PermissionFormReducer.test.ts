import {Actions, reducer} from './PermissionFormReducer';

test('it updates the state with the action ENABLE_ALL_OWN', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    };
    expect(reducer(state, {type: Actions.ENABLE_ALL_OWN})).toEqual({
        own: {
            all: true,
            identifiers: [],
        },
        edit: {
            all: true,
            identifiers: [],
        },
        view: {
            all: true,
            identifiers: [],
        },
    });
});

test('it updates the state with the action ENABLE_ALL_EDIT', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    };
    expect(reducer(state, {type: Actions.ENABLE_ALL_EDIT})).toEqual({
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: true,
            identifiers: [],
        },
        view: {
            all: true,
            identifiers: [],
        },
    });
});

test('it updates the state with the action ENABLE_ALL_VIEW', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    };
    expect(reducer(state, {type: Actions.ENABLE_ALL_VIEW})).toEqual({
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: true,
            identifiers: [],
        },
    });
});

test('it updates the state with the action DISABLE_ALL_OWN', () => {
    const state = {
        own: {
            all: true,
            identifiers: [],
        },
        edit: {
            all: true,
            identifiers: [],
        },
        view: {
            all: true,
            identifiers: [],
        },
    };
    expect(reducer(state, {type: Actions.DISABLE_ALL_OWN})).toEqual({
        own: {
            all: false,
            identifiers: [],
        },
        edit: {
            all: true,
            identifiers: [],
        },
        view: {
            all: true,
            identifiers: [],
        },
    });
});

test('it updates the state with the action DISABLE_ALL_EDIT', () => {
    const state = {
        own: {
            all: true,
            identifiers: [],
        },
        edit: {
            all: true,
            identifiers: [],
        },
        view: {
            all: true,
            identifiers: [],
        },
    };
    expect(reducer(state, {type: Actions.DISABLE_ALL_EDIT})).toEqual({
        own: {
            all: false,
            identifiers: [],
        },
        edit: {
            all: false,
            identifiers: [],
        },
        view: {
            all: true,
            identifiers: [],
        },
    });
});

test('it updates the state with the action DISABLE_ALL_VIEW', () => {
    const state = {
        own: {
            all: true,
            identifiers: [],
        },
        edit: {
            all: true,
            identifiers: [],
        },
        view: {
            all: true,
            identifiers: [],
        },
    };
    expect(reducer(state, {type: Actions.DISABLE_ALL_VIEW})).toEqual({
        own: {
            all: false,
            identifiers: [],
        },
        edit: {
            all: false,
            identifiers: [],
        },
        view: {
            all: false,
            identifiers: [],
        },
    });
});

test('it updates the state with the action DISABLE_ALL_VIEW and empties own and edit', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a', 'b'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: true,
            identifiers: [],
        },
    };
    expect(reducer(state, {type: Actions.DISABLE_ALL_VIEW})).toEqual({
        own: {
            all: false,
            identifiers: [],
        },
        edit: {
            all: false,
            identifiers: [],
        },
        view: {
            all: false,
            identifiers: [],
        },
    });
});

test('it updates the state with the action DISABLE_ALL_EDIT and empties own', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a', 'b'],
        },
        edit: {
            all: true,
            identifiers: [],
        },
        view: {
            all: true,
            identifiers: [],
        },
    };
    expect(reducer(state, {type: Actions.DISABLE_ALL_EDIT})).toEqual({
        own: {
            all: false,
            identifiers: [],
        },
        edit: {
            all: false,
            identifiers: [],
        },
        view: {
            all: true,
            identifiers: [],
        },
    });
});

test('it updates the state with the action CLEAR_OWN', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    };
    expect(reducer(state, {type: Actions.CLEAR_OWN})).toEqual({
        own: {
            all: false,
            identifiers: [],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    });
});

test('it updates the state with the action CLEAR_EDIT', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    };
    expect(reducer(state, {type: Actions.CLEAR_EDIT})).toEqual({
        own: {
            all: false,
            identifiers: [],
        },
        edit: {
            all: false,
            identifiers: [],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    });
});

test('it updates the state with the action CLEAR_VIEW', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    };
    expect(reducer(state, {type: Actions.CLEAR_VIEW})).toEqual({
        own: {
            all: false,
            identifiers: [],
        },
        edit: {
            all: false,
            identifiers: [],
        },
        view: {
            all: false,
            identifiers: [],
        },
    });
});

test('it updates the state with the action ADD_TO_OWN', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    };
    expect(reducer(state, {type: Actions.ADD_TO_OWN, identifier: 'new'})).toEqual({
        own: {
            all: false,
            identifiers: ['a', 'new'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b', 'new'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c', 'new'],
        },
    });
});

test('it updates the state with the action ADD_TO_EDIT', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    };
    expect(reducer(state, {type: Actions.ADD_TO_EDIT, identifier: 'new'})).toEqual({
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b', 'new'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c', 'new'],
        },
    });
});

test('it updates the state with the action ADD_TO_VIEW', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    };
    expect(reducer(state, {type: Actions.ADD_TO_VIEW, identifier: 'new'})).toEqual({
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c', 'new'],
        },
    });
});

test('it updates the state with the action REMOVE_FROM_OWN', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    };
    expect(reducer(state, {type: Actions.REMOVE_FROM_OWN, identifier: 'a'})).toEqual({
        own: {
            all: false,
            identifiers: [],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    });
});

test('it updates the state with the action REMOVE_FROM_EDIT', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    };
    expect(reducer(state, {type: Actions.REMOVE_FROM_EDIT, identifier: 'a'})).toEqual({
        own: {
            all: false,
            identifiers: [],
        },
        edit: {
            all: false,
            identifiers: ['b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    });
});

test('it updates the state with the action REMOVE_FROM_VIEW', () => {
    const state = {
        own: {
            all: false,
            identifiers: ['a'],
        },
        edit: {
            all: false,
            identifiers: ['a', 'b'],
        },
        view: {
            all: false,
            identifiers: ['a', 'b', 'c'],
        },
    };
    expect(reducer(state, {type: Actions.REMOVE_FROM_VIEW, identifier: 'a'})).toEqual({
        own: {
            all: false,
            identifiers: [],
        },
        edit: {
            all: false,
            identifiers: ['b'],
        },
        view: {
            all: false,
            identifiers: ['b', 'c'],
        },
    });
});
