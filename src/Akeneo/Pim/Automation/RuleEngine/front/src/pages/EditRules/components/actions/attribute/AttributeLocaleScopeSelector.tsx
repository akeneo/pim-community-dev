import React from 'react';
import {
  Attribute,
  AttributeCode,
  Locale,
  LocaleCode,
  ScopeCode,
} from '../../../../../models';
import { AttributeSelector } from '../../../../../components/Selectors/AttributeSelector';
import {
  getScopeValidation,
  ScopeSelector,
} from '../../../../../components/Selectors/ScopeSelector';
import {
  getLocaleValidation,
  LocaleSelector,
} from '../../../../../components/Selectors/LocaleSelector';
import {
  useBackboneRouter,
  useTranslate,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';
import { getAttributeByIdentifier } from '../../../../../repositories/AttributeRepository';
import { useFormContext } from 'react-hook-form';
import { IndexedScopes } from '../../../../../repositories/ScopeRepository';
import styled from 'styled-components';
import { InlineHelper } from '../../../../../components/HelpersInfos/InlineHelper';
import { ActionFormContainer } from '../style';

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
  scopeValue?: ScopeCode;
  localeValue?: LocaleCode;
  filterAttributeTypes?: string[];
  disabled?: boolean;
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
  scopeValue,
  localeValue,
  filterAttributeTypes,
  disabled,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const currentCatalogLocale = useUserCatalogLocale();
  const { watch, setValue, clearError } = useFormContext();
  const [attribute, setAttribute] = React.useState<Attribute | null>(null);
  const [attributeIsChanged, setAttributeIsChanged] = React.useState<boolean>(
    false
  );
  const [firstRefresh, setFirstRefresh] = React.useState<boolean>(true);

  const refreshAttribute: (
    attributeCode: AttributeCode | null
  ) => void = async (attributeCode: AttributeCode | null) => {
    const attribute = attributeCode
      ? await getAttributeByIdentifier(attributeCode, router)
      : null;
    setAttribute(attribute);
    if (onAttributeChange) {
      onAttributeChange(attribute);
    }
    setFirstRefresh(false);
  };

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

  const onAttributeCodeChange = (value: AttributeCode | null) => {
    refreshAttribute(value);
    setAttributeIsChanged(true);
    clearError(attributeFormName);
  };

  const validateAttribute = async (value: any): Promise<string | true> => {
    if (!value) {
      return translate('pimee_catalog_rule.exceptions.required_attribute');
    }

    const attribute = await getAttributeByIdentifier(value, router);
    if (null === attribute) {
      return `${translate(
        'pimee_catalog_rule.exceptions.unknown_attribute'
      )} ${translate(
        'pimee_catalog_rule.exceptions.select_another_attribute_or_remove_action'
      )}`;
    }

    return true;
  };

  const onScopeCodeChange = () => {
    if (
      !getAvailableLocales()
        .map(locale => locale.code)
        .includes(getLocaleFormValue())
    ) {
      setValue(localeFormName, undefined);
    }
  };

  React.useEffect(() => {
    if (attributeCode) {
      refreshAttribute(attributeCode);
    }
  }, []);

  const isDisabled = () => disabled ?? (!firstRefresh && null === attribute);

  return (
    <ActionFormContainer>
      <SelectorBlock>
        <AttributeSelector
          data-testid={attributeId}
          name={attributeFormName}
          label={attributeLabel}
          currentCatalogLocale={currentCatalogLocale}
          value={attributeCode}
          onChange={onAttributeCodeChange}
          placeholder={attributePlaceholder}
          validation={{ validate: validateAttribute }}
          filterAttributeTypes={filterAttributeTypes}
          disabled={disabled}
        />
      </SelectorBlock>
      {null === attribute && !firstRefresh && (
        <SelectorBlock>
          <InlineHelper danger>
            {`${translate(
              'pimee_catalog_rule.exceptions.unknown_attribute'
            )} ${translate(
              'pimee_catalog_rule.exceptions.select_another_attribute'
            )} ${translate('pimee_catalog_rule.exceptions.or')} `}
            <a href={`#${router.generate(`pim_enrich_attribute_create`)}`}>
              {translate('pimee_catalog_rule.exceptions.create_attribute_link')}
            </a>
          </InlineHelper>
        </SelectorBlock>
      )}
      {(attribute?.scopable || (!attributeIsChanged && scopeValue)) && (
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
            value={scopeValue}
            onChange={onScopeCodeChange}
            allowClear={!attribute?.scopable}
            disabled={isDisabled()}
            validation={scopeValidation}
          />
        </SelectorBlock>
      )}
      {(attribute?.localizable || (!attributeIsChanged && localeValue)) && (
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
            value={localeValue}
            allowClear={!attribute?.localizable}
            disabled={isDisabled()}
            validation={localeValidation}
          />
        </SelectorBlock>
      )}
    </ActionFormContainer>
  );
};
