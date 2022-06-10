import React, {FC, useState} from 'react';
import {List} from 'akeneo-design-system';
import {Criteria} from './models/Criteria';
import {useCatalogCriteria} from './hooks/useCatalogCriteria';
import {Empty} from './components/Empty';

type Props = {
    id: string;
};

const ProductSelection: FC<Props> = ({id}) => {
    const backend = useCatalogCriteria(id);

    const [criteria, setCriteria] = useState<Criteria[]>(backend);

    if (0 === criteria.length) {
        return <Empty />;
    }

    return (
        <List>
            {criteria.map(criterion => {
                const Module = criterion.module;

                const handleChange = (values: object) => {
                    setCriteria(state =>
                        state.map(old =>
                            criterion.id !== old.id
                                ? old
                                : {
                                      ...old,
                                      ...values,
                                  }
                        )
                    );
                };

                return <Module key={criterion.id} value={criterion} onChange={handleChange} />;
            })}
        </List>
    );
};

export {ProductSelection};
