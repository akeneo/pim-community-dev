import {AttributesIllustration, Button, Field, Helper, Modal, SelectInput, TextInput} from 'akeneo-design-system';
import React from 'react';
import {ColumnCode, ColumnDefinition, DataType, isColumnCodeNotAvailable} from '../models';
import {LabelCollection, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {LocaleLabel} from './LocaleLabel';
import {FieldsList} from '../shared';

export type DataTypesMapping = {
  [dataType: string]: {
    useable_as_first_column?: boolean;
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
};

type ErrorValidations = {
  code: string[];
  data_type: string[];
};

const AddColumnModal: React.FC<AddColumnModalProps> = ({close, onCreate, existingColumnCodes, dataTypesMapping}) => {
  const userContext = useUserContext();
  const translate = useTranslate();
  const catalogLocale = userContext.get('catalogLocale');
  const labelRef: React.RefObject<HTMLInputElement> = React.createRef();

  const [columnDefinition, setColumnDefinition] = React.useState<UndefinedColumnDefinition>({
    code: '',
    label: '',
    data_type: null,
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
    return validateCode(columnDefinition.code, silent) + validateDataType(columnDefinition.data_type, silent) === 0;
  };

  const handleCreate = () => {
    const labels: LabelCollection = {};
    labels[catalogLocale] = columnDefinition.label;
    close();
    onCreate({
      code: columnDefinition.code,
      labels: labels,
      data_type: columnDefinition.data_type,
      validations: {},
    } as ColumnDefinition);
  };

  const dataTypes: DataType[] = (
    existingColumnCodes.length
      ? Object.keys(dataTypesMapping)
      : Object.keys(dataTypesMapping).filter((dataType: string) => dataTypesMapping[dataType].useable_as_first_column)
  ) as DataType[];

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
          <SelectInput
            emptyResultLabel={translate('pim_common.select2.no_match')}
            onChange={(value: string | null) => {
              handleDataTypeChange((value || null) as DataType);
            }}
            openLabel={translate('pim_common.open')}
            placeholder={translate('pim_table_attribute.form.attribute.select_type')}
            value={columnDefinition.data_type as DataType}
            clearable={false}
          >
            {dataTypes.map(dataType => (
              <SelectInput.Option
                key={dataType}
                title={translate(`pim_table_attribute.properties.data_type.${dataType}`)}
                value={dataType}
              >
                {translate(`pim_table_attribute.properties.data_type.${dataType}`)}
              </SelectInput.Option>
            ))}
          </SelectInput>
          {!existingColumnCodes.length && (
            <Helper>{translate('pim_table_attribute.form.attribute.first_column_type_helper')}</Helper>
          )}
        </Field>
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
