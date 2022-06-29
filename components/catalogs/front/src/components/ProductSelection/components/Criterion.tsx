import React, {FC, memo, useCallback} from 'react';
import {AnyCriterionState} from '../models/Criteria';
import {getCriterionByFieldType} from '../criteria/getCriterionByFieldType';
import {useProductSelectionContext} from '../contexts/ProductSelectionContext';
import {ProductSelectionActions} from '../reducers/ProductSelectionReducer';
import {CriterionErrors} from '../models/CriterionErrors';

type Props = {
    id: string;
    state: AnyCriterionState;
    errors: CriterionErrors;
};

export const Criterion: FC<Props> = memo(({id, state, errors}) => {
    const dispatch = useProductSelectionContext();
    const {component: Component} = getCriterionByFieldType(state.field);

    const handleChange = useCallback(
        (newState: AnyCriterionState) => {
            dispatch({
                type: ProductSelectionActions.UPDATE_CRITERION,
                id: id,
                state: newState,
            });
        },
        [dispatch, id]
    );

    const handleRemove = useCallback(() => {
        dispatch({
            type: ProductSelectionActions.REMOVE_CRITERION,
            id: id,
        });
    }, [dispatch, id]);

    return <Component state={state} errors={errors} onChange={handleChange} onRemove={handleRemove} />;
});
