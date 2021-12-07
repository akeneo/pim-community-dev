import {Field} from 'akeneo-design-system';
import React from 'react';
import {RecordColumnDefinition} from '../../models';
import {ColumnProperties} from './index';
import {ReferenceEntitySelector} from '../ReferenceEntitySelector';
import {useTranslate} from '@akeneo-pim-community/shared';

const TextProperties: ColumnProperties = ({selectedColumn}) => {
  const translate = useTranslate();
  const recordColumn = selectedColumn as RecordColumnDefinition;

  return (
    <Field label={translate('pim_table_attribute.form.attribute.reference_entity')}>
      <ReferenceEntitySelector readOnly value={recordColumn.properties.reference_entity_identifier} />
    </Field>
  );
};

export default TextProperties;
