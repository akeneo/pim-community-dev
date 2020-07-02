import React, {ChangeEvent, useEffect, useState} from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AttributeOption, Locale} from '../model';
import {useLocalesContext} from '../contexts';

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

    const onSubmit = (event: any) => {
        event.preventDefault();
        saveAttributeOption(updatedOption);
    };

    return (
        <form className="AknSubsection AknAttributeOption-edit" onSubmit={(event: any) => onSubmit(event)}>
            <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
                <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_labels')}</span>
            </div>
            <div className="AknAttributeOption-edit-translations">
                {locales.map((locale: Locale) => {
                    return (
                        <div className="AknFieldContainer" key={`${option.code}-${locale.code}`}>
                            <div className="AknFieldContainer-header">
                                <label className="AknFieldContainer-label control-label AknFieldContainer-label">
                                    {locale.label}
                                </label>
                            </div>
                            <div className="AknFieldContainer-inputContainer field-input">
                                <input
                                    type="text"
                                    className="AknTextField"
                                    defaultValue={option.optionValues[locale.code].value}
                                    role="attribute-option-label"
                                    onChange={(event: ChangeEvent<HTMLInputElement>) => onUpdateOptionLabel(event, locale.code)}
                                    data-locale={locale.code}
                                />
                            </div>
                        </div>
                    );
                })}
            </div>

            <div className="AknAttributeOption-edit-saveTranslations">
                <button className="AknButton AknButton--apply save" role="save-options-translations" type="submit">
                    {translate('pim_common.done')}
                </button>
            </div>
        </form>
    );
};

export default Edit;
