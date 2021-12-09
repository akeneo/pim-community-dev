import {Field} from 'akeneo-design-system';
import React from 'react';
import {RecordColumnDefinition} from '../../models';
import {ColumnProperties} from './index';
import {ReferenceEntitySelector} from '../ReferenceEntitySelector';
import {useTranslate} from '@akeneo-pim-community/shared';

const RecordProperties: ColumnProperties = ({selectedColumn}) => {
  const translate = useTranslate();
  const recordColumn = selectedColumn as RecordColumnDefinition;

  return (
    <Field label={translate('pim_table_attribute.form.attribute.reference_entity')}>
      <ReferenceEntitySelector readOnly value={recordColumn.reference_entity_code} />
    </Field>
  );
};

export default RecordProperties;
