import React, {FC, useEffect, useReducer} from 'react';
import {getColor} from 'akeneo-design-system';
import styled from 'styled-components';
import {ProductSelectionReducer} from './reducers/ProductSelectionReducer';
import {ProductSelectionContext} from './contexts/ProductSelectionContext';
import {Criterion} from './components/Criterion';
import {Empty} from './components/Empty';
import {ProductSelectionValues} from './models/ProductSelectionValues';
import {CriterionErrors} from './models/CriterionErrors';
import {AddCriterionDropdown} from './components/AddCriterionDropdown';

const Header = styled.div`
    border-bottom: 1px solid ${getColor('grey', 60)};
    display: flex;
    justify-content: end;
    padding: 10px 0;
`;

export type ProductSelectionErrors = {
    [key in keyof ProductSelectionValues]?: CriterionErrors;
};

const emptyErrors: CriterionErrors = {
    value: null,
    operator: null,
    field: null,
};

type Props = {
    criteria: ProductSelectionValues;
    onChange: (values: ProductSelectionValues) => void;
    errors: ProductSelectionErrors;
};

const ProductSelection: FC<Props> = ({criteria, onChange, errors}) => {
    const [values, dispatch] = useReducer(ProductSelectionReducer, criteria);

    useEffect(() => {
        if (criteria !== values) {
            onChange(values);
        }
    }, [criteria, values, onChange]);

    const rows = Object.keys(values).map(id => (
        <Criterion key={id} id={id} state={values[id]} errors={errors[id] || emptyErrors} />
    ));

    return (
        <ProductSelectionContext.Provider value={dispatch}>
            <Header>
                <AddCriterionDropdown />
            </Header>
            {rows.length ? rows : <Empty />}
        </ProductSelectionContext.Provider>
    );
};

export {ProductSelection};
