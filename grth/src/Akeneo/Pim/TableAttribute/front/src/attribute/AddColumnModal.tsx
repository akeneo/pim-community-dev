import {AttributesIllustration, Button, Field, Helper, Modal, TextInput} from 'akeneo-design-system';
import React from 'react';
import {
  ColumnCode,
  ColumnDefinition,
  DataType,
  isColumnCodeNotAvailable,
  RecordColumnDefinition,
  ReferenceEntityIdentifierOrCode,
} from '../models';
import {LabelCollection, useFeatureFlags, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {LocaleLabel} from './LocaleLabel';
import {FieldsList} from '../shared';
import {DataTypeSelector} from './DataTypeSelector';
import {ReferenceEntitySelector} from './ReferenceEntitySelector';

export type DataTypesMapping = {
  [dataType: string]: {
    useable_as_first_column?: boolean;
    flag?: string;
  };
};

type AddColumnModalProps = {
  close: () => void;
  onCreate: (columnDefinition: ColumnDefinition) => void;
  existingColumnCodes: ColumnCode[];
  dataTypesMapping: DataTypesMapping;
};

type UndefinedColumnDefinition = {
  code: ColumnCode;
  label: string;
  data_type: DataType | null;
  reference_entity_identifier: ReferenceEntityIdentifierOrCode | undefined;
};

type ErrorValidations = {
  code: string[];
  data_type: string[];
};

const AddColumnModal: React.FC<AddColumnModalProps> = ({close, onCreate, existingColumnCodes, dataTypesMapping}) => {
  const userContext = useUserContext();
  const translate = useTranslate();
  const featureFlags = useFeatureFlags();
  const catalogLocale = userContext.get('catalogLocale');
  const labelRef: React.RefObject<HTMLInputElement> = React.createRef();

  const [columnDefinition, setColumnDefinition] = React.useState<UndefinedColumnDefinition>({
    code: '',
    label: '',
    data_type: null,
    reference_entity_identifier: undefined,
  });

  const [errorValidations, setErrorValidations] = React.useState<ErrorValidations>({
    code: [],
    data_type: [],
  });

  const [dirtyCode, setDirtyCode] = React.useState<boolean>(false);

  const focus = (labRef: React.RefObject<HTMLInputElement>) => {
    labRef?.current?.focus();
  };

  React.useEffect(() => {
    focus(labelRef);
  }, []);

  const handleLabelChange = (label: string) => {
    setColumnDefinition(columnDefinition => {
      return {...columnDefinition, label};
    });
    if (!dirtyCode) {
      const code = label.replace(/[^a-zA-Z0-9_]/gi, '_').substring(0, 100);
      setColumnDefinition(columnDefinition => {
        return {...columnDefinition, code};
      });
      validateCode(code, false);
    }
  };

  const handleCodeChange = (code: ColumnCode) => {
    setColumnDefinition({...columnDefinition, code});
    validateCode(code, false);
    setDirtyCode(code !== '');
  };

  const handleReferenceEntityChange = (referenceEntityIdentifier: ReferenceEntityIdentifierOrCode | undefined) => {
    setColumnDefinition(columnDefinition => {
      return {...columnDefinition, reference_entity_identifier: referenceEntityIdentifier};
    });
  };

  const handleDataTypeChange = (data_type: DataType | null) => {
    setColumnDefinition({...columnDefinition, data_type});
    validateDataType(data_type, false);
  };

  const validateCode = (code: ColumnCode, silent: boolean): number => {
    const validations: string[] = [];
    if (code === '') validations.push(translate('pim_table_attribute.validations.column_code_must_be_filled'));
    if (isColumnCodeNotAvailable(code))
      validations.push(translate('pim_table_attribute.validations.not_available_code'));
    if (code !== '' && !/^[a-zA-Z0-9_]+$/.exec(code))
      validations.push(translate('pim_table_attribute.validations.invalid_column_code'));
    if (existingColumnCodes.includes(code))
      validations.push(
        translate('pim_table_attribute.validations.duplicated_column_code', {
          duplicateCode: code,
        })
      );

    if (!silent) {
      setErrorValidations(oldValidations => {
        return {...oldValidations, code: validations};
      });
    }
    return validations.length;
  };

  const validateDataType = (dataType: DataType | null, silent: boolean): number => {
    const validations: string[] = [];
    if (dataType === null)
      validations.push(translate('pim_table_attribute.validations.column_data_type_must_be_filled'));
    if (!silent) {
      setErrorValidations(oldValidations => {
        return {...oldValidations, data_type: validations};
      });
    }
    return validations.length;
  };

  const isValid = (silent: boolean) => {
    if (validateCode(columnDefinition.code, silent) + validateDataType(columnDefinition.data_type, silent) > 0)
      return false;
    if (columnDefinition.data_type === 'record' && !columnDefinition.reference_entity_identifier) return false;
    return true;
  };

  const handleCreate = () => {
    const labels: LabelCollection = {};
    labels[catalogLocale] = columnDefinition.label;
    close();
    const newColumn = {
      code: columnDefinition.code,
      labels: labels,
      data_type: columnDefinition.data_type,
      validations: {},
    } as ColumnDefinition;
    if (columnDefinition.reference_entity_identifier) {
      (newColumn as RecordColumnDefinition).reference_entity_identifier = columnDefinition.reference_entity_identifier;
    }
    onCreate(newColumn);
  };

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={close} illustration={<AttributesIllustration />}>
      <Modal.SectionTitle color='brand'>
        {translate('pim_table_attribute.form.attribute.table_attribute')}
      </Modal.SectionTitle>
      <Modal.Title>{translate('pim_table_attribute.form.attribute.add_column')}</Modal.Title>
      <FieldsList>
        <Field label={translate('pim_common.label')} locale={<LocaleLabel localeCode={catalogLocale} />}>
          <TextInput
            ref={labelRef}
            value={columnDefinition.label}
            onChange={handleLabelChange}
            maxLength={250}
            characterLeftLabel={translate(
              'pim_common.characters_left',
              {count: 250 - columnDefinition.label.length},
              250 - columnDefinition.label.length
            )}
          />
        </Field>
        <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
          <TextInput
            value={columnDefinition.code}
            onChange={handleCodeChange}
            maxLength={100}
            characterLeftLabel={translate(
              'pim_common.characters_left',
              {count: 100 - columnDefinition.code.length},
              100 - columnDefinition.code.length
            )}
          />
          {errorValidations.code.map((validation, i) => (
            <Helper level='error' key={i}>
              {validation}
            </Helper>
          ))}
        </Field>
        <Field
          label={translate('pim_table_attribute.form.attribute.data_type')}
          requiredLabel={translate('pim_common.required_label')}
        >
          <DataTypeSelector
            dataType={columnDefinition.data_type}
            onChange={handleDataTypeChange}
            isFirstColumn={existingColumnCodes.length === 0}
            dataTypesMapping={dataTypesMapping}
          />
          {!existingColumnCodes.length && (
            <Helper>
              {translate(
                featureFlags.isEnabled('reference_entity')
                  ? 'pim_table_attribute.form.attribute.first_column_type_helper_with_record'
                  : 'pim_table_attribute.form.attribute.first_column_type_helper'
              )}
            </Helper>
          )}
        </Field>
        {columnDefinition.data_type === 'record' && (
          <Field label={translate('pim_table_attribute.form.attribute.reference_entity')}>
            <ReferenceEntitySelector
              emptyResultLabel={translate('pim_common.no_result')}
              onChange={handleReferenceEntityChange}
              openLabel={translate('pim_common.open')}
              clearLabel={translate('pim_common.clear_value')}
              value={columnDefinition.reference_entity_identifier}
            />
          </Field>
        )}
      </FieldsList>
      <Modal.BottomButtons>
        <Button level='primary' onClick={handleCreate} disabled={!isValid(true)} tabIndex={0}>
          {translate('pim_common.create')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {AddColumnModal};
