import React, {ChangeEvent, FC, useCallback, useEffect, useState} from 'react';

import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AttributeOption, Locale} from '../model';
import {EditingOptionContextProvider, useAttributeOptionsContext, useLocalesContext} from '../contexts';
import AttributeOptionForm from './AttributeOptionForm';

interface EditProps {
    option: AttributeOption;
}

const Edit: FC<EditProps> = ({option}) => {
    const translate = useTranslate();
    const locales = useLocalesContext();
    const {save} = useAttributeOptionsContext();
    const [updatedOption, setUpdatedOption] = useState<AttributeOption>(option);

    useEffect(() => {
        setUpdatedOption(option);
    }, [option]);

    const onUpdateOptionLabel = useCallback((event: ChangeEvent<HTMLInputElement>, localeCode: string) => {
        event.persist();
        let updatedOption: AttributeOption = {...option};
        updatedOption.optionValues[localeCode].value = event.target.value;
        setUpdatedOption(updatedOption);
    }, [setUpdatedOption]);

    const onSubmit = useCallback((event: any) => {
        event.preventDefault();
        save(updatedOption);
    }, [save]);

    return (
        <EditingOptionContextProvider option={option}>
            <form className="AknSubsection AknAttributeOption-edit" onSubmit={(event: any) => onSubmit(event)}>
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
                    <button className="AknButton AknButton--apply save" role="save-options-translations" type="submit">
                        {translate('pim_common.done')}
                    </button>
                </div>
            </form>
        </EditingOptionContextProvider>
    );
};

export default Edit;
