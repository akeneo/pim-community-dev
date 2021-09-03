const unique = <T>(entries: T[]): T[] => {
    return entries.filter((value, index, self) => self.indexOf(value) === index);
};

const remove = <T>(entries: T[], entryToRemove: T): T[] => {
    return entries.filter(entry => entry !== entryToRemove);
};

export type PermissionFormState = {
    own: {
        all: boolean,
        identifiers: string[];
    },
    edit: {
        all: boolean,
        identifiers: string[];
    },
    view: {
        all: boolean,
        identifiers: string[];
    },
};

export const permissionFormInitialState = {
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

export enum PermissionFormActions {
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

type PermissionFormAction =
    | { type: PermissionFormActions.ENABLE_ALL_OWN }
    | { type: PermissionFormActions.DISABLE_ALL_OWN }
    | { type: PermissionFormActions.ENABLE_ALL_EDIT }
    | { type: PermissionFormActions.DISABLE_ALL_EDIT }
    | { type: PermissionFormActions.ENABLE_ALL_VIEW }
    | { type: PermissionFormActions.DISABLE_ALL_VIEW }
    | { type: PermissionFormActions.CLEAR_OWN }
    | { type: PermissionFormActions.CLEAR_EDIT }
    | { type: PermissionFormActions.CLEAR_VIEW }
    | { type: PermissionFormActions.ADD_TO_OWN, identifier: string }
    | { type: PermissionFormActions.ADD_TO_EDIT, identifier: string }
    | { type: PermissionFormActions.ADD_TO_VIEW, identifier: string }
    | { type: PermissionFormActions.REMOVE_FROM_OWN, identifier: string }
    | { type: PermissionFormActions.REMOVE_FROM_EDIT, identifier: string }
    | { type: PermissionFormActions.REMOVE_FROM_VIEW, identifier: string };

export const permissionFormReducer = (state: PermissionFormState, action: PermissionFormAction): PermissionFormState => {
    switch (action.type) {
        case PermissionFormActions.ENABLE_ALL_OWN:
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

        case PermissionFormActions.DISABLE_ALL_OWN:
            return {
                ...state,
                own: {
                    all: false,
                    identifiers: [],
                },
            };

        case PermissionFormActions.ENABLE_ALL_EDIT:
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

        case PermissionFormActions.DISABLE_ALL_EDIT:
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

        case PermissionFormActions.ENABLE_ALL_VIEW:
            return {
                ...state,
                view: {
                    all: true,
                    identifiers: [],
                },
            };

        case PermissionFormActions.DISABLE_ALL_VIEW:
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

        case PermissionFormActions.CLEAR_OWN:
            return {
                ...state,
                own: {
                    all: false,
                    identifiers: [],
                },
            };

        case PermissionFormActions.CLEAR_EDIT:
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

        case PermissionFormActions.CLEAR_VIEW:
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

        case PermissionFormActions.ADD_TO_OWN:
            return {
                ...state,
                own: {
                    all: false,
                    identifiers: unique([
                        ...state.own.identifiers,
                        action.identifier,
                    ]),
                },
                edit: {
                    all: false,
                    identifiers: unique([
                        ...state.edit.identifiers,
                        action.identifier,
                    ]),
                },
                view: {
                    all: false,
                    identifiers: unique([
                        ...state.view.identifiers,
                        action.identifier,
                    ]),
                }
            };

        case PermissionFormActions.ADD_TO_EDIT:
            return {
                ...state,
                edit: {
                    all: false,
                    identifiers: unique([
                        ...state.edit.identifiers,
                        action.identifier,
                    ]),
                },
                view: {
                    all: false,
                    identifiers: unique([
                        ...state.view.identifiers,
                        action.identifier,
                    ]),
                }
            };

        case PermissionFormActions.ADD_TO_VIEW:
            return {
                ...state,
                view: {
                    all: false,
                    identifiers: unique([
                        ...state.view.identifiers,
                        action.identifier,
                    ]),
                }
            };

        case PermissionFormActions.REMOVE_FROM_OWN:
            return {
                ...state,
                own: {
                    all: false,
                    identifiers: remove(state.own.identifiers, action.identifier),
                },
            }

        case PermissionFormActions.REMOVE_FROM_EDIT:
            return {
                ...state,
                own: {
                    all: false,
                    identifiers: remove(state.own.identifiers, action.identifier),
                },
                edit: {
                    all: false,
                    identifiers: remove(state.edit.identifiers, action.identifier),
                },
            }

        case PermissionFormActions.REMOVE_FROM_VIEW:
            return {
                ...state,
                own: {
                    all: false,
                    identifiers: remove(state.own.identifiers, action.identifier),
                },
                edit: {
                    all: false,
                    identifiers: remove(state.edit.identifiers, action.identifier),
                },
                view: {
                    all: false,
                    identifiers: remove(state.view.identifiers, action.identifier),
                },
            }
    }

    /* istanbul ignore next */
    return state;
};
