import React from 'react';
import { Controller } from 'react-hook-form';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';
import { FamilySelector } from '../../../../components/Selectors/FamilySelector';
import { ActionTitle } from './ActionLine';
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
        handleDelete={handleDelete}>
        <ActionTitle>
          {translate(
            'pimee_catalog_rule.form.edit.actions.set_family.subtitle'
          )}
        </ActionTitle>
        <div className={'AknFormContainer'}>
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
          />
        </div>
      </ActionTemplate>
    </>
  );
};

export { SetFamilyActionLine };
