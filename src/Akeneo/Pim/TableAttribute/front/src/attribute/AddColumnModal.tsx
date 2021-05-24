import { AttributesIllustration, Button, Field, Locale, Modal, SelectInput, TextInput, Helper } from "akeneo-design-system";
import React from "react";
import { ColumnDefinition, ColumnType } from "../models/TableConfiguration";
import { useUserContext, useTranslate } from '@akeneo-pim-community/shared';

type AddColumnModalProps = {
  close: () => void;
  onCreate: (columnDefinition: ColumnDefinition) => void;
}

type UndefinedColumnDefinition = {
  code: string;
  label: string;
  data_type: ColumnType | null
}

type Validations = {
  code: string[];
  data_type: string[];
};

const AddColumnModal: React.FC<AddColumnModalProps> = ({
  close,
  onCreate
}) => {
  const userContext = useUserContext();
  const translate = useTranslate();

  const catalogLocale = userContext.get('catalogLocale');
  const [ columnDefinition, setColumnDefinition ] = React.useState<UndefinedColumnDefinition>({
    code: '',
    label: '',
    data_type: null
  });

  const [ validations, setValidations ] = React.useState<Validations>({
    code: [],
    data_type: [],
  });

  const [ dirtyCode, setDirtyCode ] = React.useState<boolean>(false);

  const dataTypes = [
    { value: 'text', label: 'TODO Text' }
  ];

  const handleLabelChange = (label: string) => {
    setColumnDefinition(columnDefinition => { return {...columnDefinition, label}});
    if (!dirtyCode) {
      const code = label.replace(/[^a-zA-Z0-9_]/gi, '_').substring(0, 100);
      setColumnDefinition(columnDefinition => { return {...columnDefinition, code} });
      validateCode(code, false);
    }
  }

  const handleCodeChange = (code: string) => {
    setColumnDefinition({...columnDefinition, code});
    validateCode(code, false);
    setDirtyCode(code !== '');
  }

  const handleDataTypeChange = (data_type: ColumnType | null) => {
    setColumnDefinition({...columnDefinition, data_type});
    validateDataType(data_type, false);
  }

  const validateCode = (code: string, silent: boolean): number => {
    const validations: string[] = [];
    if (code === '') validations.push('TODO Should not be empty');
    if (code !== '' && !code.match(/^[a-zA-Z0-9_]+$/)) validations.push('TODO Invalid code');

    if (!silent) {
      setValidations(oldValidations => {
        return {...oldValidations, code: validations}
      });
    }
    return validations.length;
  }

  const validateDataType = (dataType: ColumnType | null, silent: boolean): number => {
    const validations: string[] = [];
    if (dataType === null) {
      validations.push('TODO SHOULD not be mpty');
    }
    if (!silent) {
      setValidations(oldValidations => {
        return {...oldValidations, data_type: validations}
      });
    }
    return validations.length;
  }

  const isValid = (silent: boolean) => {
    return validateCode(columnDefinition.code, silent) + validateDataType(columnDefinition.data_type, silent) === 0;
  }

  const handleCreate = () => {
    if (!isValid(false)) {
      return;
    }

    const labels = {};
    labels[catalogLocale] = columnDefinition.label;
    close();
    onCreate({
      code: columnDefinition.code,
      labels: labels,
      data_type: columnDefinition.data_type as ColumnType,
    });
  }

  return <Modal closeTitle="TODO Close" onClose={close} illustration={<AttributesIllustration />}>
    <Modal.SectionTitle color="brand">TODO Table attribute</Modal.SectionTitle>
    <Modal.Title>TODO Add a new column</Modal.Title>
    TODO Lorem ipsum.
    <div>
      <Field
        label={'TODO Label'}
        locale={<Locale code={catalogLocale} languageLabel={'TODO Fetch locale to get language'}/>}
      >
        <TextInput value={columnDefinition.label} onChange={handleLabelChange}/>
      </Field>
      <Field label={'TODO Code'} requiredLabel={'required TODO'}>
        <TextInput
          value={columnDefinition.code}
          onChange={handleCodeChange}
          maxLength={100}
          /* TODO Change key */
          characterLeftLabel={translate(
            'pim_datagrid.workflow.characters_left',
            {count: 100 - columnDefinition.code.length},
            100 - columnDefinition.code.length
          )}
        />
        {validations.code.map((validation, i) => <Helper level="error" key={i}>
            {validation}
          </Helper>
        )}
      </Field>
      <Field label={'TODO Data type'} requiredLabel={'required TODO'}>
        <SelectInput
          emptyResultLabel="TODO No result found"
          onChange={(value: string | null) => { handleDataTypeChange((value || null) as ColumnType) }}
          openLabel="TODO Open label"
          placeholder="TODO Please enter a value in the Select input"
          value={columnDefinition.data_type as string}
          clearable={false}
        >
          {dataTypes.map(dataType => <SelectInput.Option
            key={dataType.value}
            title={dataType.label}
            value={dataType.value}
          >{dataType.value}</SelectInput.Option>)}
        </SelectInput>
      </Field>
    </div>
    <Modal.BottomButtons>
      <Button level="primary" onClick={handleCreate} disabled={!isValid(true)}>
        Create
      </Button>
    </Modal.BottomButtons>
  </Modal>;
}

export { AddColumnModal };
