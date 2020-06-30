import React from 'react';
import { Attribute, AttributeCode, Locale } from '../../../../../models';
import { useFormContext } from 'react-hook-form';
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
import { Controller } from 'react-hook-form';
import { useControlledFormInputAction } from '../../../hooks';

import { IndexedScopes } from '../../../../../repositories/ScopeRepository';
import styled from 'styled-components';
import { InlineHelper } from '../../../../../components/HelpersInfos/InlineHelper';
import { ActionFormContainer } from '../style';

const SelectorBlock = styled.div`
  margin-bottom: 15px;
`;

const ErrorBlock = styled.div`
  margin-top: 5px;
`;

type Props = {
  attributeCode: AttributeCode | null;
  attributeFormName: string;
  attributeId: string;
  attributeLabel: string;
  attributePlaceholder: string;
  scopeId: string;
  scopeLabel?: string;
  localeId: string;
  localeLabel?: string;
  locales: Locale[];
  scopes: IndexedScopes;
  onAttributeChange?: (attribute: Attribute | null) => void;
  lineNumber: number;
  filterAttributeTypes?: string[];
  disabled?: boolean;
};

export const AttributeLocaleScopeSelector: React.FC<Props> = ({
  attributeFormName,
  attributeCode,
  attributeId,
  attributeLabel,
  attributePlaceholder,
  scopeId,
  scopeLabel,
  localeId,
  localeLabel,
  locales,
  scopes,
  onAttributeChange,
  lineNumber,
  filterAttributeTypes,
  disabled,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const currentCatalogLocale = useUserCatalogLocale();

  const {
    scopeFormName,
    localeFormName,
    getScopeFormValue,
    getLocaleFormValue,
  } = useControlledFormInputAction<string>(lineNumber);

  const { clearError } = useFormContext();
  const [attribute, setAttribute] = React.useState<Attribute | null>(null);
  const [attributeIsChanged, setAttributeIsChanged] = React.useState<boolean>(
    false
  );

  const refreshAttribute: (attributeCode: AttributeCode | null) => void = async (attributeCode) => {
    const attribute = attributeCode ?
      await getAttributeByIdentifier(attributeCode, router) :
      undefined;
    setAttribute(attribute);
    if (onAttributeChange) {
      onAttributeChange(attribute || null);
    }
  };

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

  const onAttributeCodeChange = (value: any) => {
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

  React.useEffect(() => {
    if (attributeCode) {
      refreshAttribute(attributeCode);
    }
  }, []);

  React.useEffect(() => {
    setAttribute(undefined);
    refreshAttribute(attributeCode);
    setAttributeIsChanged(false);
  }, [attributeCode])

  const isDisabled = () => disabled ?? null === attribute;

  return (
    <ActionFormContainer>
      <SelectorBlock
        className={
          null === attribute && !firstRefresh ? 'select2-container-error' : ''
        }>
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
        {null === attribute && !firstRefresh && (
          <ErrorBlock>
            <InlineHelper danger>
              {`${translate(
                'pimee_catalog_rule.exceptions.unknown_attribute'
              )} ${translate(
                'pimee_catalog_rule.exceptions.select_another_attribute'
              )} ${translate('pimee_catalog_rule.exceptions.or')} `}
              <a href={`#${router.generate(`pim_enrich_attribute_create`)}`}>
                {translate(
                  'pimee_catalog_rule.exceptions.create_attribute_link'
                )}
              </a>
            </InlineHelper>
          </ErrorBlock>
        )}
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
      {(attribute?.scopable ||
        (!attributeIsChanged && getScopeFormValue())) && (
        <SelectorBlock>
          <Controller
            as={ScopeSelector}
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
            allowClear={!attribute?.scopable}
            disabled={!firstRefresh && null === attribute}
            rules={getScopeValidation(attribute, scopes, translate)}
          />
        </SelectorBlock>
      )}
      {(attribute?.localizable ||
        (!attributeIsChanged && getLocaleFormValue())) && (
        <SelectorBlock>
          <Controller
            as={LocaleSelector}
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
            rules={getLocaleValidation(
              attribute,
              locales,
              getAvailableLocales(),
              getScopeFormValue(),
              translate
            )}
            disabled={isDisabled()}
          />
        </SelectorBlock>
      )}
    </ActionFormContainer>
  );
};
