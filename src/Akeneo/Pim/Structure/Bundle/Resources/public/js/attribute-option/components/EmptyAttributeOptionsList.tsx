import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useAttributeOptionsContext} from '../hooks';

const EmptyAttributeOptionsList: FC = () => {
    const translate = useTranslate();
    const {activateCreation} = useAttributeOptionsContext();

    return (
        <div className="AknAttributeOption-emptyList">
            <img src="/bundles/pimui/images/illustrations/Attribute.svg"/>
            <div className="AknAttributeOption-emptyList-message">
                {translate('pim_enrich.entity.attribute_option.module.edit.no_options_msg')}
            </div>
            <div
                className="AknAttributeOption-emptyList-addLink"
                role="add-new-attribute-option-button"
                onClick={activateCreation}
            >
                {translate('pim_enrich.entity.attribute_option.module.edit.add_option')}
            </div>
        </div>
    );
};

export default EmptyAttributeOptionsList;
