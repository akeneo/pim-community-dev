import React, {FC, useState} from 'react';
import {List} from 'akeneo-design-system';
import {Criterion, CriterionState} from './models/Criterion';
import {useCatalogCriteria} from './hooks/useCatalogCriteria';
import {Empty} from './components/Empty';

type Props = {
    id: string;
};

const ProductSelection: FC<Props> = ({id}) => {
    const backend = useCatalogCriteria(id);

    const [criteria, setCriteria] = useState<Criterion<any>[]>(backend);

    if (0 === criteria.length) {
        return <Empty />;
    }

    return (
        <List>
            {criteria.map(criterion => {
                const Module = criterion.module;

                const handleChange = (criterionState: CriterionState) => {
                    setCriteria(state =>
                        state.map(old =>
                            criterion.id !== old.id
                                ? old
                                : {
                                      ...old,
                                      state: criterionState,
                                  }
                        )
                    );
                };

                return <Module key={criterion.id} state={criterion.state} onChange={handleChange} />;
            })}
        </List>
    );
};

export {ProductSelection};
