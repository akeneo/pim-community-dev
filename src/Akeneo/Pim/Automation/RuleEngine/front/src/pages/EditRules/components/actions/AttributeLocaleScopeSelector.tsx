import React from 'react';
import {
  Attribute,
  AttributeCode,
  Locale,
  LocaleCode,
  ScopeCode,
} from '../../../../models';
import { AttributeSelector } from '../../../../components/Selectors/AttributeSelector';
import {
  getScopeValidation,
  ScopeSelector,
} from '../../../../components/Selectors/ScopeSelector';
import {
  getLocaleValidation,
  LocaleSelector,
} from '../../../../components/Selectors/LocaleSelector';
import {
  useBackboneRouter,
  useTranslate,
  useUserCatalogLocale,
} from '../../../../dependenciesTools/hooks';
import { getAttributeByIdentifier } from '../../../../repositories/AttributeRepository';
import { useFormContext } from 'react-hook-form';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';
import styled from 'styled-components';

const SelectorBlock = styled.div`
  margin-bottom: 15px;
`;

type Props = {
  attributeCode: AttributeCode | null;
  attributeFormName: string;
  attributeId: string;
  attributeLabel: string;
  attributePlaceholder: string;
  scopeId: string;
  scopeFormName: string;
  scopeLabel?: string;
  localeId: string;
  localeLabel?: string;
  localeFormName: string;
  locales: Locale[];
  scopes: IndexedScopes;
  onAttributeChange?: (attribute: Attribute | null) => void;
};

export const AttributeLocaleScopeSelector: React.FC<Props> = ({
  attributeCode,
  attributeId,
  attributeLabel,
  attributePlaceholder,
  attributeFormName,
  scopeId,
  scopeLabel,
  scopeFormName,
  localeId,
  localeLabel,
  localeFormName,
  locales,
  scopes,
  onAttributeChange,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const currentCatalogLocale = useUserCatalogLocale();
  const { watch, setValue } = useFormContext();
  const [attribute, setAttribute] = React.useState<Attribute | null>(null);
  const [attributeIsChanged, setAttributeIsChanged] = React.useState<boolean>(
    false
  );
  const [firstRefresh, setFirstRefresh] = React.useState<boolean>(true);

  const refreshAttribute: (
    attributeCode: AttributeCode | null
  ) => void = async (attributeCode: AttributeCode | null) => {
    const attribute =
      attributeCode !== null
        ? await getAttributeByIdentifier(attributeCode, router)
        : null;
    setAttribute(attribute);
    if (onAttributeChange) {
      onAttributeChange(attribute);
    }
    setFirstRefresh(false);
  };

  const getAttributeFormValue: () => AttributeCode = () =>
    watch(attributeFormName);
  const getScopeFormValue: () => ScopeCode = () => watch(scopeFormName);
  const getLocaleFormValue: () => LocaleCode = () => watch(localeFormName);

  const getAvailableLocales = (): Locale[] => {
    if (!attribute?.scopable) {
      return locales;
    }

    const scopeCode = getScopeFormValue();
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].locales;
    }

    return [];
  };

  const [localeValidation, setLocaleValidation] = React.useState(
    attribute
      ? getLocaleValidation(
          attribute,
          locales,
          getAvailableLocales(),
          getScopeFormValue(),
          translate
        )
      : {}
  );
  const [scopeValidation, setScopeValidation] = React.useState(
    attribute ? getScopeValidation(attribute, scopes, translate) : {}
  );
  React.useEffect(() => {
    setLocaleValidation(
      attribute
        ? getLocaleValidation(
            attribute,
            locales,
            getAvailableLocales(),
            getScopeFormValue(),
            translate
          )
        : {}
    );
    setScopeValidation(
      attribute ? getScopeValidation(attribute, scopes, translate) : {}
    );
  }, [JSON.stringify(getAvailableLocales())]);

  const setAttributeFormValue = (value: AttributeCode | null) => {
    refreshAttribute(value);
    setAttributeIsChanged(true);
  };

  const setScopeFormValue = () => {
    if (
      !getAvailableLocales()
        .map(locale => locale.code)
        .includes(getLocaleFormValue())
    ) {
      setValue(localeFormName, undefined);
    }
  };

  React.useEffect(() => {
    refreshAttribute(attributeCode);
  }, []);

  return (
    <div className={'AknFormContainer'}>
      <SelectorBlock>
        <AttributeSelector
          data-testid={attributeId}
          name={attributeFormName}
          label={attributeLabel}
          currentCatalogLocale={currentCatalogLocale}
          value={getAttributeFormValue()}
          onChange={setAttributeFormValue}
          placeholder={attributePlaceholder}
          disabled={!firstRefresh && null === attribute}
          validation={{
            required: translate(
              'pimee_catalog_rule.exceptions.required_attribute'
            ),
          }}
        />
      </SelectorBlock>
      {(attribute?.scopable ||
        (!attributeIsChanged && getScopeFormValue())) && (
        <SelectorBlock>
          <ScopeSelector
            data-testid={scopeId}
            name={scopeFormName}
            label={
              scopeLabel ||
              `${translate(
                'pim_enrich.entity.channel.uppercase_label'
              )} ${translate('pim_common.required_label')}`
            }
            availableScopes={Object.values(scopes)}
            currentCatalogLocale={currentCatalogLocale}
            value={getScopeFormValue()}
            onChange={setScopeFormValue}
            allowClear={!attribute?.scopable}
            disabled={!firstRefresh && null === attribute}
            validation={scopeValidation}
          />
        </SelectorBlock>
      )}
      {(attribute?.localizable ||
        (!attributeIsChanged && getLocaleFormValue())) && (
        <SelectorBlock>
          <LocaleSelector
            data-testid={localeId}
            name={localeFormName}
            label={
              localeLabel ||
              `${translate(
                'pim_enrich.entity.locale.uppercase_label'
              )} ${translate('pim_common.required_label')}`
            }
            availableLocales={getAvailableLocales()}
            value={getLocaleFormValue()}
            allowClear={!attribute?.localizable}
            disabled={!firstRefresh && null === attribute}
            validation={localeValidation}
          />
        </SelectorBlock>
      )}
    </div>
  );
};
