import React from 'react';
import {Criterion as Actual} from '../Criterion';
import {useProductSelectionContext} from '../../contexts/ProductSelectionContext';
import {ProductSelectionActions} from '../../reducers/ProductSelectionReducer';

const Criterion: typeof Actual = jest.fn(({id, state}) => {
    const dispatch = useProductSelectionContext();

    const toggle = () =>
        dispatch({
            type: ProductSelectionActions.UPDATE_CRITERION,
            id: id,
            state: {
                ...state,
                value: !state.value,
            },
        });

    return (
        <div>
            <div>{`[criterion:${state.field}]`}</div>
            <button onClick={toggle}>[ToggleCriterionValue]</button>
        </div>
    );
});

export {Criterion};
