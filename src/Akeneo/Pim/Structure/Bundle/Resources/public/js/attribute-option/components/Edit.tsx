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
    const [updatedOption, setUpdatedOption] = useState<AttributeOption | null>(null);

    useEffect(() => {
        setUpdatedOption(null);
    }, [option]);

    const onUpdateOptionLabel = (event: ChangeEvent<HTMLInputElement>, localeCode: string) => {
        event.persist();
        let updatedOption: AttributeOption = {...option};
        updatedOption.optionValues[localeCode].value = event.target.value;
        setUpdatedOption(updatedOption);
    };

    const saveOption = () => {
        if (updatedOption !== null) {
            saveAttributeOption(updatedOption);
            setUpdatedOption(null);
        }
    };

    return (
        <div className="AknSubsection AknAttributeOption-edit">
            <div className="AknSubsection-title AknSubsection-title--glued tabsection-title">
                <span>{translate('pim_enrich.entity.attribute_option.module.edit.options_labels')}</span>
            </div>
            <div>
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
                                />
                            </div>
                        </div>
                    );
                })}
                <button className="AknButton AknButton--apply save" role="save-options-translations" onClick={() => saveOption()}>
                    {translate('pim_common.done')}
                </button>

            </div>
        </div>
    );
};

export default Edit;
