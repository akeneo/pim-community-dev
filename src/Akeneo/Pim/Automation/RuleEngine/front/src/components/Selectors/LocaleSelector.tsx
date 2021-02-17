import React from 'react';
import {
  Attribute,
  getAttributeLabel,
  Locale,
  LocaleCode,
  ScopeCode,
} from '../../models';
import {
  Select2Option,
  Select2OptionGroup,
  Select2SimpleSyncWrapper,
  Select2Value,
} from '../Select2Wrapper';
import {useTranslate} from '../../dependenciesTools/hooks';
import {Translate} from '../../dependenciesTools';

const getLocaleValidation = (
  attribute: Attribute | null,
  locales: Locale[],
  availableLocales: Locale[],
  channelCode: ScopeCode,
  translate: Translate,
  currentCatalogLocale: LocaleCode
) => {
  const localeValidation: any = {};
  if (!attribute) {
    return localeValidation;
  }
  if (attribute.localizable) {
    localeValidation['required'] = translate(
      'pimee_catalog_rule.exceptions.required_locale',
      {
        attributeLabel: getAttributeLabel(attribute, currentCatalogLocale),
      }
    );
  }
  localeValidation['validate'] = (localeCode: any) => {
    if (attribute.localizable) {
      if (!locales.some(locale => locale.code === localeCode)) {
        return translate(
          'pimee_catalog_rule.exceptions.unknown_or_inactive_locale',
          {localeCode}
        );
      }
      if (!availableLocales.some(locale => locale.code === localeCode)) {
        if (!!channelCode && attribute.scopable) {
          return translate('pimee_catalog_rule.exceptions.unbound_locale', {
            localeCode,
            channelCode,
          });
        } else if (!attribute.scopable) {
          return translate(
            'pimee_catalog_rule.exceptions.unknown_or_inactive_locale',
            {localeCode}
          );
        }
      }
    } else {
      if (localeCode) {
        return translate(
          'pimee_catalog_rule.exceptions.locale_on_unlocalizable_attribute'
        );
      }
    }
    return true;
  };

  return localeValidation;
};

type Props = {
  label?: string;
  hiddenLabel?: boolean;
  availableLocales: Locale[];
  value?: LocaleCode;
  onChange?: (value: LocaleCode) => void;
  allowClear?: boolean;
  disabled?: boolean;
  name?: string;
  validation?: {required?: string; validate?: (value: any) => string | true};
  placeholder?: string;
  containerCssClass?: string;
  displayAsCode?: boolean;
};

const LocaleSelector: React.FC<Props> = ({
  label,
  hiddenLabel = false,
  availableLocales,
  value,
  onChange,
  children,
  allowClear = false,
  disabled = false,
  validation,
  placeholder,
  displayAsCode = false,
  ...remainingProps
}) => {
  const translate = useTranslate();
  const localeChoices = availableLocales.map((locale: Locale) => {
    return {
      id: locale.code,
      text: locale.language,
    };
  });

  if (value && !localeChoices.some(localeChoice => localeChoice.id === value)) {
    localeChoices.push({
      id: value,
      text: `[${value}]`,
    });
  }

  const formatLocale = (item: Select2Option | Select2OptionGroup): string => {
    const locale = availableLocales.find(
      (locale: Locale) => locale.code === item.id
    );
    if (!locale) {
      return item.id as string;
    }
    const localeCode = locale.code;
    const shortRegion = localeCode.toLowerCase().split('_')[
      localeCode.split('_').length - 1
    ];

    return `<i class="flag flag-${shortRegion}"}/>&nbsp;${
      displayAsCode ? locale.code : locale.language
    }`;
  };

  const handleChange = (value: Select2Value) => {
    if (onChange) {
      onChange(value as LocaleCode);
    }
  };

  return (
    <>
      <Select2SimpleSyncWrapper
        {...remainingProps}
        label={label || translate('pim_enrich.entity.locale.uppercase_label')}
        hiddenLabel={hiddenLabel}
        data={localeChoices}
        hideSearch={true}
        formatResult={formatLocale}
        formatSelection={formatLocale}
        placeholder={
          placeholder || translate('pim_enrich.entity.locale.uppercase_label')
        }
        value={value || null}
        onChange={handleChange}
        allowClear={allowClear}
        disabled={disabled}
        validation={validation}
        dropdownCssClass='locale-dropdown'
      />
      {children}
    </>
  );
};

export {getLocaleValidation, LocaleSelector};
