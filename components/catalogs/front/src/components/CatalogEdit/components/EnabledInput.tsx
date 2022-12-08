import React, {FC, useCallback} from 'react';
import {BooleanInput, Field} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useCatalogFormContext} from '../contexts/CatalogFormContext';
import {CatalogFormActions} from '../reducers/CatalogFormReducer';

type Props = {
    value: boolean;
    error: string | undefined;
};

const EnabledInput: FC<Props> = ({value, error}) => {
    const translate = useTranslate();
    const dispatch = useCatalogFormContext();

    const handleStatusChange = useCallback(
        value => {
            dispatch({type: CatalogFormActions.SET_ENABLED, value: value});
        },
        [dispatch]
    );

    return (
        <Field label={translate('akeneo_catalogs.catalog_status_widget.fields.enable_catalog')}>
            <BooleanInput
                noLabel={translate('akeneo_catalogs.catalog_status_widget.inputs.no')}
                value={value}
                yesLabel={translate('akeneo_catalogs.catalog_status_widget.inputs.yes')}
                readOnly={false}
                onChange={handleStatusChange}
                invalid={error !== undefined}
                size={'small'}
            >
                {error}
            </BooleanInput>
        </Field>
    );
};

export {EnabledInput};
