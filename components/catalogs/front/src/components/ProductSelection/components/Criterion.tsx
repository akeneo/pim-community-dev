import React, {FC, memo, useCallback, useEffect, useState} from 'react';
import {AnyCriterionState, CriterionModule} from '../models/Criterion';
import {useProductSelectionContext} from '../contexts/ProductSelectionContext';
import {ProductSelectionActions} from '../reducers/ProductSelectionReducer';
import {CriterionErrors} from '../models/CriterionErrors';
import {useCriteriaRegistry} from '../criteria/useCriteriaRegistry';

type Props = {
    id: string;
    state: AnyCriterionState;
    errors: CriterionErrors;
};

export const Criterion: FC<Props> = memo(({id, state, errors}) => {
    const dispatch = useProductSelectionContext();
    const {getCriterionByField} = useCriteriaRegistry();
    const [Component, setComponent] = useState<FC<CriterionModule<AnyCriterionState>>>();

    useEffect(() => {
        getCriterionByField(state.field).then(criterion => setComponent(() => criterion.component));
    }, [getCriterionByField, state.field, setComponent]);

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

    if (!Component) {
        return null;
    }

    return <Component state={state} errors={errors} onChange={handleChange} onRemove={handleRemove} />;
});
