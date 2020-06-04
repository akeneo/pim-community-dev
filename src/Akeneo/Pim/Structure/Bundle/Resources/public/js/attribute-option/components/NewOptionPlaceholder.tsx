import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const NewOptionPlaceholder = () => {
    const translate = useTranslate();

    return (
        <div className="AknAttributeOption-listItem AknAttributeOption-listItem--selected" role="new-option-placeholder">
            <span className="AknAttributeOption-itemCode">
                {translate('pim_enrich.entity.attribute_option.module.edit.new_option_code')}
            </span>
        </div>
    );
};

export default NewOptionPlaceholder;
