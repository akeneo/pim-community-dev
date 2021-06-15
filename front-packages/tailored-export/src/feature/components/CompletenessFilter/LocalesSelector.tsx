import React from "react";
import {useTranslate, LocaleCode} from '@akeneo-pim-community/shared';

type LocalesSelectorProps = {
    locales: LocaleCode[];
    onChange: (newLocales: LocaleCode[]) => void;
}
const LocalesSelector = ({}: LocalesSelectorProps) => {
    const translate = useTranslate();

    return (
      <span>
        {translate('pim_connector.export.completeness.locale_selector.label')}
      </span>
    );
};

export {LocalesSelector};
