import React, {FC, memo, useCallback, useEffect, useState} from 'react';
import {AnyCriterion, AnyCriterionState} from '../models/Criterion';
import {useProductSelectionContext} from '../contexts/ProductSelectionContext';
import {ProductSelectionActions} from '../reducers/ProductSelectionReducer';
import {CriterionErrors} from '../models/CriterionErrors';
import {useFindCriterionByField} from '../hooks/useFindCriterionByField';

type Props = {
    id: string;
    state: AnyCriterionState;
    errors: CriterionErrors;
};

export const Criterion: FC<Props> = memo(({id, state, errors}) => {
    const dispatch = useProductSelectionContext();
    const findCriterionByField = useFindCriterionByField();
    const [criterion, setCriterion] = useState<AnyCriterion>();

    useEffect(() => {
        findCriterionByField(state.field).then(criterion => setCriterion(criterion));
    }, [findCriterionByField, state.field, setCriterion]);

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

    const Component = criterion?.component;

    if (!Component) {
        return null;
    }

    return <Component state={state} errors={errors} onChange={handleChange} onRemove={handleRemove} />;
});
