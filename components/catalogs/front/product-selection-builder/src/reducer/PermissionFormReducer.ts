const unique = <T>(entries: T[]): T[] => {
    return entries.filter((value, index, self) => self.indexOf(value) === index);
};

const remove = <T>(entries: T[], entryToRemove: T): T[] => {
    return entries.filter(entry => entry !== entryToRemove);
};

export type State = {
    own?: {
        all: boolean;
        identifiers: string[];
    };
    edit: {
        all: boolean;
        identifiers: string[];
    };
    view: {
        all: boolean;
        identifiers: string[];
    };
};

export enum Actions {
    ENABLE_ALL_OWN = 'ENABLE_ALL_OWN',
    DISABLE_ALL_OWN = 'DISABLE_ALL_OWN',
    ENABLE_ALL_EDIT = 'ENABLE_ALL_EDIT',
    DISABLE_ALL_EDIT = 'DISABLE_ALL_EDIT',
    ENABLE_ALL_VIEW = 'ENABLE_ALL_VIEW',
    DISABLE_ALL_VIEW = 'DISABLE_ALL_VIEW',
    CLEAR_OWN = 'CLEAR_OWN',
    CLEAR_EDIT = 'CLEAR_EDIT',
    CLEAR_VIEW = 'CLEAR_VIEW',
    ADD_TO_OWN = 'ADD_TO_OWN',
    ADD_TO_EDIT = 'ADD_TO_EDIT',
    ADD_TO_VIEW = 'ADD_TO_VIEW',
    REMOVE_FROM_OWN = 'REMOVE_FROM_OWN',
    REMOVE_FROM_EDIT = 'REMOVE_FROM_EDIT',
    REMOVE_FROM_VIEW = 'REMOVE_FROM_VIEW',
}

export type Action =
    | {type: Actions.ENABLE_ALL_OWN}
    | {type: Actions.DISABLE_ALL_OWN}
    | {type: Actions.ENABLE_ALL_EDIT}
    | {type: Actions.DISABLE_ALL_EDIT}
    | {type: Actions.ENABLE_ALL_VIEW}
    | {type: Actions.DISABLE_ALL_VIEW}
    | {type: Actions.CLEAR_OWN}
    | {type: Actions.CLEAR_EDIT}
    | {type: Actions.CLEAR_VIEW}
    | {type: Actions.ADD_TO_OWN; identifier: string}
    | {type: Actions.ADD_TO_EDIT; identifier: string}
    | {type: Actions.ADD_TO_VIEW; identifier: string}
    | {type: Actions.REMOVE_FROM_OWN; identifier: string}
    | {type: Actions.REMOVE_FROM_EDIT; identifier: string}
    | {type: Actions.REMOVE_FROM_VIEW; identifier: string};

export const reducer = <T extends State>(state: T, action: Action): T => {
    switch (action.type) {
        case Actions.ENABLE_ALL_OWN:
            return {
                ...state,
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

        case Actions.DISABLE_ALL_OWN:
            return {
                ...state,
                own: {
                    all: false,
                    identifiers: [],
                },
            };

        case Actions.ENABLE_ALL_EDIT:
            return {
                ...state,
                edit: {
                    all: true,
                    identifiers: [],
                },
                view: {
                    all: true,
                    identifiers: [],
                },
            };

        case Actions.DISABLE_ALL_EDIT:
            return {
                ...state,
                ...(state.own && {
                    own: {
                        all: false,
                        identifiers: [...state.own.identifiers],
                    },
                }),
                edit: {
                    all: false,
                    identifiers: [...(state.own?.identifiers || [])],
                },
            };

        case Actions.ENABLE_ALL_VIEW:
            return {
                ...state,
                view: {
                    all: true,
                    identifiers: [],
                },
            };

        case Actions.DISABLE_ALL_VIEW:
            return {
                ...state,
                ...(state.own && {
                    own: {
                        all: false,
                        identifiers: [...state.own.identifiers],
                    },
                }),
                edit: {
                    all: false,
                    identifiers: [...state.edit.identifiers],
                },
                view: {
                    all: false,
                    identifiers: [...state.edit.identifiers],
                },
            };

        case Actions.CLEAR_OWN:
            return {
                ...state,
                own: {
                    all: false,
                    identifiers: [],
                },
            };

        case Actions.CLEAR_EDIT:
            return {
                ...state,
                own: {
                    all: false,
                    identifiers: [],
                },
                edit: {
                    all: false,
                    identifiers: [],
                },
            };

        case Actions.CLEAR_VIEW:
            return {
                ...state,
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
            };

        case Actions.ADD_TO_OWN:
            return {
                ...state,
                own: {
                    all: false,
                    identifiers: unique([...(state.own?.identifiers || []), action.identifier]),
                },
                edit: {
                    ...state.edit,
                    identifiers: state.edit.all ? [] : unique([...state.edit.identifiers, action.identifier]),
                },
                view: {
                    ...state.view,
                    identifiers: state.view.all ? [] : unique([...state.view.identifiers, action.identifier]),
                },
            };

        case Actions.ADD_TO_EDIT:
            return {
                ...state,
                edit: {
                    all: false,
                    identifiers: unique([...state.edit.identifiers, action.identifier]),
                },
                view: {
                    ...state.view,
                    identifiers: state.view.all ? [] : unique([...state.view.identifiers, action.identifier]),
                },
            };

        case Actions.ADD_TO_VIEW:
            return {
                ...state,
                view: {
                    all: false,
                    identifiers: unique([...state.view.identifiers, action.identifier]),
                },
            };

        case Actions.REMOVE_FROM_OWN:
            return {
                ...state,
                own: {
                    all: false,
                    identifiers: remove(state.own?.identifiers || [], action.identifier),
                },
            };

        case Actions.REMOVE_FROM_EDIT:
            return {
                ...state,
                ...(state.own && {
                    own: {
                        all: false,
                        identifiers: remove(state.own.identifiers, action.identifier),
                    },
                }),
                edit: {
                    all: false,
                    identifiers: remove(state.edit.identifiers, action.identifier),
                },
            };

        case Actions.REMOVE_FROM_VIEW:
            return {
                ...state,
                ...(state.own && {
                    own: {
                        all: false,
                        identifiers: remove(state.own.identifiers, action.identifier),
                    },
                }),
                edit: {
                    all: false,
                    identifiers: remove(state.edit.identifiers, action.identifier),
                },
                view: {
                    all: false,
                    identifiers: remove(state.view.identifiers, action.identifier),
                },
            };
    }

    /* istanbul ignore next */
    return state;
};
