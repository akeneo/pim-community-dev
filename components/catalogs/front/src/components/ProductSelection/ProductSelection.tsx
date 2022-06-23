import React, {FC, useCallback} from 'react';
import {getColor} from 'akeneo-design-system';
import {Criterion} from './models/Criterion';
import {Criteria, CriterionStates} from './models/Criteria';
import {AddCriterionDropdown} from './components/AddCriterionDropdown';
import styled from 'styled-components';
import {Empty} from './components/Empty';

const Header = styled.div`
    border-bottom: 1px solid ${getColor('grey', 60)};
    display: flex;
    justify-content: end;
    padding: 10px 0;
`;

type Props = {
    criteria: Criteria;
    setCriteria: (criteria: Criteria) => void;
    onChange: (isDirty: boolean) => void;
};

const ProductSelection: FC<Props> = ({criteria, setCriteria, onChange}) => {
    const addCriterion = useCallback(
        (criterion: Criterion<CriterionStates>) => {
            setCriteria([...criteria, criterion]);
            onChange(true);
        },
        [criteria, setCriteria, onChange]
    );

    const updateCriterion = useCallback(
        (criterion: Criterion<CriterionStates>, newState: CriterionStates) => {
            setCriteria(
                criteria.map(old =>
                    criterion.id !== old.id
                        ? old
                        : {
                              ...old,
                              state: newState,
                          }
                )
            );
            onChange(true);
        },
        [criteria, setCriteria, onChange]
    );

    const removeCriterion = useCallback(
        (criterion: Criterion<CriterionStates>) => {
            setCriteria(criteria.filter(old => old.id !== criterion.id));
            onChange(true);
        },
        [criteria, setCriteria, onChange]
    );

    const list = criteria.map(criterion => {
        const Module = criterion.module;

        const handleChange = (newState: CriterionStates) => updateCriterion(criterion, newState);
        const handleRemove = () => removeCriterion(criterion);

        return <Module key={criterion.id} state={criterion.state} onChange={handleChange} onRemove={handleRemove} />;
    });

    return (
        <>
            <Header>
                <AddCriterionDropdown onNewCriterion={addCriterion} />
            </Header>
            {criteria.length ? list : <Empty />}
        </>
    );
};

export {ProductSelection};
