import React from 'react';
import { Controller } from 'react-hook-form';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { FamilySelector } from '../../../../components/Selectors/FamilySelector';
import {
  ActionGrid,
  ActionTitle,
  AknActionFormContainer,
  ActionLeftSide,
} from './ActionLine';
import { useTranslate } from '../../../../dependenciesTools/hooks';
import { useControlledFormInputAction } from '../../hooks';

const SetFamilyActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  handleDelete,
  currentCatalogLocale,
}) => {
  const translate = useTranslate();

  const {
    fieldFormName,
    typeFormName,
    valueFormName,
    getValueFormValue,
    isFormFieldInError,
  } = useControlledFormInputAction<string>(lineNumber);

  return (
    <>
      <Controller
        name={fieldFormName}
        as={<input type='hidden' />}
        defaultValue='family'
      />
      <Controller
        name={typeFormName}
        as={<input type='hidden' />}
        defaultValue='set'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.set_family.title'
        )}
        helper='This feature is under development. Please use the import to manage your rules.'
        legend='This feature is under development. Please use the import to manage your rules.'
        handleDelete={handleDelete}
        lineNumber={lineNumber}>
        <ActionGrid>
          <ActionLeftSide>
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.set_family.subtitle'
              )}
            </ActionTitle>
            <AknActionFormContainer
              className={
                isFormFieldInError('value') ? 'select2-container-error' : ''
              }>
              <Controller
                as={FamilySelector}
                label={`${translate(
                  'pim_enrich.entity.family.uppercase_label'
                )} ${translate('pim_common.required_label')}`}
                currentCatalogLocale={currentCatalogLocale}
                value={getValueFormValue()}
                placeholder={translate(
                  'pimee_catalog_rule.form.edit.actions.set_family.subtitle'
                )}
                name={valueFormName}
                rules={{
                  required: translate(
                    'pimee_catalog_rule.exceptions.required_value'
                  ),
                }}
              />
            </AknActionFormContainer>
          </ActionLeftSide>
        </ActionGrid>
      </ActionTemplate>
    </>
  );
};

export { SetFamilyActionLine };
