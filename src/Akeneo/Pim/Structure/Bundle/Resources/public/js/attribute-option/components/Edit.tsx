import React, {ChangeEvent, useEffect, useState} from 'react';

import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AttributeOption, Locale} from '../model';
import {useLocalesContext} from '../contexts';
import {OptionFormContextProvider} from "../contexts/OptionFormContext";
import AttributeOptionForm from './AttributeOptionForm';

interface EditProps {
    option: AttributeOption;
    saveAttributeOption: (attributeOption: AttributeOption) => void;
}

const Edit = ({option, saveAttributeOption}: EditProps) => {
    const translate = useTranslate();
    const locales = useLocalesContext();
    const [updatedOption, setUpdatedOption] = useState<AttributeOption>(option);

    useEffect(() => {
        setUpdatedOption(option);
    }, [option]);

    const onUpdateOptionLabel = (event: ChangeEvent<HTMLInputElement>, localeCode: string) => {
        event.persist();
        let updatedOption: AttributeOption = {...option};
        updatedOption.optionValues[localeCode].value = event.target.value;
        setUpdatedOption(updatedOption);
    };

    return (
        <OptionFormContextProvider option={option}>
            <div className="AknSubsection AknAttributeOption-edit">
                <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
                    <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_labels')}</span>
                </div>
                <div className="AknAttributeOption-edit-translations">
                    {locales.map((locale: Locale) => (
                        <AttributeOptionForm key={`option-form-${option.code}-${locale.code}`}
                            option={option}
                            locale={locale}
                            onUpdateOptionLabel={onUpdateOptionLabel}
                        />
                    ))}
                </div>
                <div className="AknAttributeOption-edit-saveTranslations">
                    <button className="AknButton AknButton--apply save" role="save-options-translations"
                        onClick={() => saveAttributeOption(updatedOption)}
                    >
                        {translate('pim_common.done')}
                    </button>
                </div>
            </div>
        </OptionFormContextProvider>
    );
};


export default Edit;
