import React, {FC, useEffect, useReducer} from 'react';
import {getColor, Helper, useDebounce} from 'akeneo-design-system';
import styled from 'styled-components';
import {ProductSelectionReducer} from './reducers/ProductSelectionReducer';
import {ProductSelectionContext} from './contexts/ProductSelectionContext';
import {Criterion} from './components/Criterion';
import {Empty} from './components/Empty';
import {ProductSelectionValues} from './models/ProductSelectionValues';
import {AddCriterionDropdown} from './components/AddCriterionDropdown';
import {ProductSelectionErrors} from './models/ProductSelectionErrors';
import {CountSelectedProductsByCriteria} from './components/CountSelectedProductsByCriteria';
import {useTranslate} from '@akeneo-pim-community/shared';

const Header = styled.div`
    border-bottom: 1px solid ${getColor('grey', 60)};
    display: flex;
    justify-content: end;
    padding: 10px 0;
`;

type Props = {
    criteria: ProductSelectionValues;
    onChange: (values: ProductSelectionValues) => void;
    errors: ProductSelectionErrors;
};

const MAX_CRITERIA_PER_CATALOG = 25;

const ProductSelection: FC<Props> = ({criteria, onChange, errors}) => {
    const [values, dispatch] = useReducer(ProductSelectionReducer, criteria);
    const debouncedCriteria = useDebounce<ProductSelectionValues>(values, 2000);
    const translate = useTranslate();

    useEffect(() => {
        if (criteria !== values) {
            onChange(values);
        }
    }, [criteria, values, onChange]);

    const rows = Object.keys(values).map(id => (
        <Criterion key={id} id={id} state={values[id]} errors={errors[id] || {}} />
    ));

    return (
        <ProductSelectionContext.Provider value={dispatch}>
            {rows.length >= MAX_CRITERIA_PER_CATALOG ? (
                <Helper level={'warning'}>
                    {translate('akeneo_catalogs.product_selection.criteria.max_reached', {
                        maxCount: MAX_CRITERIA_PER_CATALOG.toString(),
                    })}
                </Helper>
            ) : null}
            <Header>
                <CountSelectedProductsByCriteria criteria={debouncedCriteria} />
                <AddCriterionDropdown isDisabled={rows.length >= MAX_CRITERIA_PER_CATALOG} />
            </Header>
            {rows.length ? rows : <Empty />}
        </ProductSelectionContext.Provider>
    );
};

export {ProductSelection};
