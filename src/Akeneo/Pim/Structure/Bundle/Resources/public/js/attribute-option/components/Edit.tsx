import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Edit = () => {
    const translate = useTranslate();

    return (
        <div className="AknSubsection AknAttributeOption-edit">
            <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
                <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_labels')}</span>
            </div>
            <div>

            </div>
        </div>
    );
};

export default Edit;
