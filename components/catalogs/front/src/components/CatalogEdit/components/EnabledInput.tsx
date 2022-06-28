import React, {FC, useCallback} from 'react';
import {BooleanInput, Field} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {useCatalogFormContext} from '../contexts/CatalogFormContext';
import {CatalogFormActions} from '../reducers/CatalogFormReducer';

const Container = styled(Field)`
    margin: 20px 0;
`;

type Props = {
    value: boolean;
    error: string | null;
};

const EnabledInput: FC<Props> = ({value, error}) => {
    const translate = useTranslate();
    const dispatch = useCatalogFormContext();

    const handleStatusChange = useCallback(
        value => {
            dispatch(value ? {type: CatalogFormActions.ENABLE} : {type: CatalogFormActions.DISABLE});
        },
        [dispatch]
    );

    return (
        <Container label={translate('akeneo_catalogs.settings.fields.enabled')}>
            <BooleanInput
                noLabel={translate('akeneo_catalogs.settings.inputs.no')}
                value={value}
                yesLabel={translate('akeneo_catalogs.settings.inputs.yes')}
                readOnly={false}
                onChange={handleStatusChange}
                invalid={error !== null}
            >
                {error}
            </BooleanInput>
        </Container>
    );
};

export {EnabledInput};
