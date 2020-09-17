import React from 'react';
import {
  Attribute,
  AttributeCode,
  AttributeType,
  Locale,
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
import { Controller } from 'react-hook-form';
import { useControlledFormInputAction } from '../../../hooks';

import { IndexedScopes } from '../../../../../repositories/ScopeRepository';
import styled from 'styled-components';
import { InlineHelper } from '../../../../../components/HelpersInfos/InlineHelper';
import { ActionFormContainer } from '../style';
import {
  createAttributeLink,
  fetchAttribute,
} from '../attribute/attribute.utils';

const SelectorBlock = styled.div`
  margin-bottom: 15px;
`;

const ErrorBlock = styled.div`
  margin-top: 5px;
`;

type Props = {
  attribute?: Attribute | null;
  attributeCode: AttributeCode;
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
  onAttributeCodeChange?: (attribute: Attribute | null) => void;
  lineNumber: number;
  filterAttributeTypes?: AttributeType[];
  disabled?: boolean;
  scopeFieldName?: string;
  localeFieldName?: string;
};

export const AttributeLocaleScopeSelector: React.FC<Props> = ({
  attribute,
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
  onAttributeCodeChange,
  lineNumber,
  filterAttributeTypes,
  disabled,
  scopeFieldName,
  localeFieldName,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const currentCatalogLocale = useUserCatalogLocale();

  const {
    formName,
    getFormValue,
    isFormFieldInError,
  } = useControlledFormInputAction<string>(lineNumber);

  const scopeFormName = formName(scopeFieldName || 'scope');
  const getScopeFormValue = () => getFormValue(scopeFieldName || 'scope');
  const localeFormName = formName(localeFieldName || 'locale');
  const getLocaleFormValue = () => getFormValue(localeFieldName || 'locale');

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

  const handleAttributeCodeChange = (value: any) => {
    const getAttribute = async (attributeCode: AttributeCode) => {
      const attribute = await fetchAttribute(router, attributeCode);
      if (onAttributeCodeChange) {
        onAttributeCodeChange(attribute);
      }
    };
    getAttribute(value);
  };

  const isDisabled = () => disabled ?? null === attribute;
  return (
    <ActionFormContainer>
      <SelectorBlock
        className={null === attribute ? 'select2-container-error' : ''}>
        <AttributeSelector
          data-testid={attributeId}
          name={attributeFormName}
          label={attributeLabel}
          currentCatalogLocale={currentCatalogLocale}
          value={attributeCode}
          onChange={handleAttributeCodeChange}
          placeholder={attributePlaceholder}
          filterAttributeTypes={filterAttributeTypes}
          disabled={disabled}
        />
        {null === attribute && (
          <ErrorBlock>
            <InlineHelper danger>
              {`${translate(
                'pimee_catalog_rule.exceptions.unknown_attribute'
              )} ${translate(
                'pimee_catalog_rule.exceptions.select_another_attribute'
              )} ${translate('pimee_catalog_rule.exceptions.or')} `}
              <a href={createAttributeLink(router, attributeCode)}>
                {translate(
                  'pimee_catalog_rule.exceptions.create_attribute_link'
                )}
              </a>
            </InlineHelper>
          </ErrorBlock>
        )}
      </SelectorBlock>
      {attribute && attribute?.scopable && (
        <SelectorBlock
          className={
            isFormFieldInError(scopeFieldName || 'scope')
              ? 'select2-container-error'
              : ''
          }>
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
            disabled={isDisabled()}
            rules={getScopeValidation(
              attribute,
              scopes,
              translate,
              currentCatalogLocale
            )}
          />
        </SelectorBlock>
      )}
      {attribute && attribute?.localizable && (
        <SelectorBlock
          className={
            isFormFieldInError(localeFieldName || 'locale')
              ? 'select2-container-error'
              : ''
          }>
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
              translate,
              currentCatalogLocale
            )}
            disabled={isDisabled()}
          />
        </SelectorBlock>
      )}
    </ActionFormContainer>
  );
};
