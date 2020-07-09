import React from 'react';
import { Attribute, AttributeCode, Locale } from '../../../../../models';
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

const SelectorBlock = styled.div`
  margin-bottom: 15px;
`;

const ErrorBlock = styled.div`
  margin-top: 5px;
`;

type Props = {
  attribute?: Attribute | null;
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
  onAttributeChange?: (attribute: AttributeCode) => void;
  lineNumber: number;
  filterAttributeTypes?: string[];
  disabled?: boolean;
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
    if (onAttributeChange) {
      onAttributeChange(value);
    }
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
          onChange={onAttributeCodeChange}
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
              <a href={`#${router.generate(`pim_enrich_attribute_create`)}`}>
                {translate(
                  'pimee_catalog_rule.exceptions.create_attribute_link'
                )}
              </a>
            </InlineHelper>
          </ErrorBlock>
        )}
      </SelectorBlock>
      {null === attribute && (
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
      {attribute && attribute?.scopable && (
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
            disabled={null === attribute}
            rules={getScopeValidation(attribute, scopes, translate)}
          />
        </SelectorBlock>
      )}
      {attribute && attribute?.localizable && (
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
