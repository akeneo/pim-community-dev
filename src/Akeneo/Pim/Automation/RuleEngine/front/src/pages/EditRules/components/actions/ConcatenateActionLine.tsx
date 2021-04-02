import React from 'react';
import {Controller, useFormContext} from 'react-hook-form';
import {ActionTemplate} from './ActionTemplate';
import {ActionLineProps} from './ActionLineProps';
import {useControlledFormInputAction} from '../../hooks';
import {
  Attribute,
  attributeAcceptsNewLine,
  AttributeCode,
  AttributeType,
  Locale,
} from '../../../../models';
import {
  createAttributeLink,
  fetchAttribute,
  useGetAttributeAtMount,
} from './attribute/attribute.utils';
import {ActionLeftSide, ActionRightSide, ActionTitle} from './ActionLine';
import {
  ActionFormContainer,
  LargeActionGrid,
  SelectorBlock,
  ErrorBlock,
} from './style';
import {AttributeSelector} from '../../../../components/Selectors/AttributeSelector';
import {InlineHelper} from '../../../../components/HelpersInfos/InlineHelper';
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
import {ConcatenatePreview} from './concatenate/ConcatenatePreview';
import {ConcatenateSourceList} from './concatenate/ConcatenateSourceList';
import {AlertDialog} from '../../../../components/AlertDialog/AlertDialog';
import {useDialogState} from 'reakit';

const targetAttributeTypes: AttributeType[] = [
  AttributeType.TEXT,
  AttributeType.TEXTAREA,
];

const ConcatenateActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  handleDelete,
  locales,
  uiLocales,
  scopes,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const currentCatalogLocale = useUserCatalogLocale();
  const removeNewLineDialog = useDialogState();
  const {setValue, watch, getValues} = useFormContext();
  const {
    formName,
    typeFormName,
    getFormValue,
    isFormFieldInError,
  } = useControlledFormInputAction<string | null>(lineNumber);
  watch(formName('source'));
  watch(formName('to.field'));
  const [attributeTarget, setAttributeTarget] = React.useState<
    Attribute | null | undefined
  >(undefined);
  const [
    attributeTargetPending,
    setAttributeTargetPending,
  ] = React.useState<Attribute | null>(null);

  const scopeFormName = formName('to.scope');
  const getScopeFormValue = () => getFormValue('to.scope');
  const localeFormName = formName('to.locale');
  const getLocaleFormValue = () => getFormValue('to.locale');

  const getAvailableLocalesForTarget = (): Locale[] => {
    if (!attributeTarget?.scopable) {
      return locales;
    }
    const scopeCode = getScopeFormValue();
    if (scopeCode && scopes[scopeCode]) {
      return scopes[scopeCode].locales;
    }
    return [];
  };

  const sourceListContainsNewLine = () => {
    const sources = watch(formName('from'));
    if (!Array.isArray(sources)) {
      return false;
    }

    return sources.some(
      (source: any) => typeof source.new_line !== 'undefined'
    );
  };

  const validatePendingAttribute = () => {
    if (!attributeTargetPending) {
      return;
    }
    setValue(formName('to.field'), attributeTargetPending.code);
    setAttributeTarget(attributeTargetPending);
  };

  const handleTargetChange = (attributeCode: AttributeCode) => {
    const getAttribute = async (attributeCode: AttributeCode) => {
      const attribute = await fetchAttribute(router, attributeCode);
      if (!attribute) {
        return;
      }
      if (!attributeAcceptsNewLine(attribute) && sourceListContainsNewLine()) {
        setAttributeTargetPending(attribute);
        removeNewLineDialog.show();
        return;
      }
      setValue(formName('to.field'), attribute.code);
      setAttributeTarget(attribute);
    };
    getAttribute(attributeCode);
  };

  const handleTargetSelecting = (event: any) => {
    event.preventDefault();
    handleTargetChange(event.val);
  };

  useGetAttributeAtMount(
    getFormValue('to.field'),
    router,
    attributeTarget,
    (attribute: Attribute | null | undefined) => {
      if (attribute || attribute === null) {
        setAttributeTarget(attribute);
        setValue(formName('to.field'), attribute?.code);
      }
    }
  );

  const isTargetDisabled = () => null === attributeTarget;

  return (
    <ActionTemplate
      title={translate(
        'pimee_catalog_rule.form.edit.actions.concatenate.title'
      )}
      helper={translate(
        'pimee_catalog_rule.form.edit.actions.concatenate.helper'
      )}
      legend={translate(
        'pimee_catalog_rule.form.edit.actions.concatenate.helper'
      )}
      handleDelete={handleDelete}
      lineNumber={lineNumber}>
      <Controller
        name={typeFormName}
        as={<span hidden />}
        defaultValue='concatenate'
        rules={{
          // There is no way to add a validation on a useFieldArray field. This is the only way to add a custom
          // validation.
          validate: () =>
            (
              getValues({nest: true})?.content?.actions?.[lineNumber]?.from ||
              []
            ).length >= 2
              ? true
              : translate(
                  'pimee_catalog_rule.exceptions.two_items_are_required'
                ),
        }}
      />
      <Controller
        name={formName('to.field')}
        as={<span hidden />}
        defaultValue={getFormValue('to.field')}
        rules={{
          required: translate('pimee_catalog_rule.exceptions.required'),
        }}
      />
      <LargeActionGrid>
        <ActionLeftSide>
          <ConcatenatePreview lineNumber={lineNumber} />
          <ConcatenateSourceList
            lineNumber={lineNumber}
            locales={locales}
            uiLocales={uiLocales}
            scopes={scopes}
            attributeTarget={attributeTarget ?? null}
          />
        </ActionLeftSide>
        <ActionRightSide>
          <ActionTitle>
            {translate(
              'pimee_catalog_rule.form.edit.actions.concatenate.select_target'
            )}
          </ActionTitle>
          <ActionFormContainer>
            <SelectorBlock
              className={
                isFormFieldInError('to.field') ? 'select2-container-error' : ''
              }>
              <AttributeSelector
                data-testid={`edit-rules-action-${lineNumber}-to-field`}
                name={formName('to.field')}
                label={`${translate(
                  'pimee_catalog_rule.form.edit.fields.attribute'
                )} ${translate('pim_common.required_label')}`}
                currentCatalogLocale={currentCatalogLocale}
                value={attributeTarget?.code || null}
                onSelecting={handleTargetSelecting}
                placeholder={translate(
                  'pimee_catalog_rule.form.edit.actions.concatenate.attribute_placeholder'
                )}
                filterAttributeTypes={targetAttributeTypes}
              />
              <AlertDialog
                dialog={removeNewLineDialog}
                onValidate={validatePendingAttribute}
                label={translate(
                  'pimee_catalog_rule.form.edit.actions.concatenate.remove_new_line_confirm.label'
                )}
                description={translate(
                  'pimee_catalog_rule.form.edit.actions.concatenate.remove_new_line_confirm.description'
                )}
              />
              {null === attributeTarget && (
                <ErrorBlock>
                  <InlineHelper danger>
                    {`${translate(
                      'pimee_catalog_rule.exceptions.unknown_attribute'
                    )} ${translate(
                      'pimee_catalog_rule.exceptions.select_another_attribute'
                    )} ${translate('pimee_catalog_rule.exceptions.or')} `}
                    <a
                      href={createAttributeLink(
                        router,
                        getFormValue('to.field')
                      )}>
                      {translate(
                        'pimee_catalog_rule.exceptions.create_attribute_link'
                      )}
                    </a>
                  </InlineHelper>
                </ErrorBlock>
              )}
            </SelectorBlock>
            {attributeTarget?.scopable && (
              <SelectorBlock
                className={
                  isFormFieldInError('to.scope')
                    ? 'select2-container-error'
                    : ''
                }>
                <Controller
                  as={ScopeSelector}
                  data-testid={`edit-rules-action-${lineNumber}-to-scope`}
                  name={scopeFormName}
                  label={`${translate(
                    'pim_enrich.entity.channel.uppercase_label'
                  )} ${translate('pim_common.required_label')}`}
                  availableScopes={Object.values(scopes)}
                  currentCatalogLocale={currentCatalogLocale}
                  value={getScopeFormValue()}
                  allowClear={!attributeTarget?.scopable}
                  disabled={isTargetDisabled()}
                  rules={getScopeValidation(
                    attributeTarget,
                    scopes,
                    translate,
                    currentCatalogLocale
                  )}
                />
              </SelectorBlock>
            )}
            {attributeTarget?.localizable && (
              <SelectorBlock
                className={
                  isFormFieldInError('to.locale')
                    ? 'select2-container-error'
                    : ''
                }>
                <Controller
                  as={LocaleSelector}
                  data-testid={`edit-rules-action-${lineNumber}-to-locale`}
                  name={localeFormName}
                  availableLocales={getAvailableLocalesForTarget()}
                  label={`${translate(
                    'pim_enrich.entity.locale.uppercase_label'
                  )} ${translate('pim_common.required_label')}`}
                  value={getLocaleFormValue()}
                  allowClear={!attributeTarget?.localizable}
                  rules={getLocaleValidation(
                    attributeTarget,
                    locales,
                    getAvailableLocalesForTarget(),
                    getScopeFormValue(),
                    translate,
                    currentCatalogLocale
                  )}
                  disabled={isTargetDisabled()}
                />
              </SelectorBlock>
            )}
          </ActionFormContainer>
        </ActionRightSide>
      </LargeActionGrid>
    </ActionTemplate>
  );
};

export {ConcatenateActionLine};
