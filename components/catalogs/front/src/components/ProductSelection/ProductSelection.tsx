import React, {FC, useCallback, useState} from 'react';
import {getColor} from 'akeneo-design-system';
import {Criterion, CriterionState} from './models/Criterion';
import {Criteria} from './models/Criteria';
import {useCatalogCriteria} from './hooks/useCatalogCriteria';
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
    id: string;
};

const ProductSelection: FC<Props> = ({id}) => {
    const backend = useCatalogCriteria(id);

    const [criteria, setCriteria] = useState<Criteria>(backend);

    const addCriterion = useCallback(
        (criterion: Criterion<CriterionState>) => {
            setCriteria(state => [...state, criterion]);
        },
        [setCriteria]
    );

    const updateCriterion = useCallback(
        (criterion: Criterion<CriterionState>, newState: CriterionState) => {
            setCriteria(state =>
                state.map(old =>
                    criterion.id !== old.id
                        ? old
                        : {
                              ...old,
                              state: newState,
                          }
                )
            );
        },
        [setCriteria]
    );

    const removeCriterion = useCallback(
        (criterion: Criterion<CriterionState>) => {
            setCriteria(state => state.filter(old => old.id !== criterion.id));
        },
        [setCriteria]
    );

    const list = criteria.map(criterion => {
        const Module = criterion.module;

        const handleChange = (newState: CriterionState) => updateCriterion(criterion, newState);
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
