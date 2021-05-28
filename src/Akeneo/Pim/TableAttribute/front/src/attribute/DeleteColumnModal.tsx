import {AttributesIllustration, Button, Field, Modal, SelectInput, TextInput, Helper} from 'akeneo-design-system';
import React from 'react';
import {ColumnDefinition, ColumnType, dataTypes} from '../models/TableConfiguration';
import {useUserContext, useTranslate} from '@akeneo-pim-community/shared';
import {LocaleLabel} from './LocaleLabel';
import styled from 'styled-components';

const FieldsList = styled.div`
  gap: 20px;
  display: flex;
  flex-direction: column;
`;

type AddColumnModalProps = {
    close: () => void;
    onCreate: (columnDefinition: ColumnDefinition) => void;
    existingColumnCodes: string[];
};

type UndefinedColumnDefinition = {
    code: string;
    label: string;
    data_type: ColumnType | null;
};

type ErrorValidations = {
    code: string[];
    data_type: string[];
};

const DeleteColumnModal: React.FC<AddColumnModalProps> = ({close, onCreate, existingColumnCodes}) => {
    const userContext = useUserContext();
    const translate = useTranslate();
    const catalogLocale = userContext.get('catalogLocale');

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

    const handleCodeChange = (code: string) => {
        setColumnDefinition({...columnDefinition, code});
        validateCode(code, false);
        setDirtyCode(code !== '');
    };

    const handleDataTypeChange = (data_type: ColumnType | null) => {
        setColumnDefinition({...columnDefinition, data_type});
        validateDataType(data_type, false);
    };

    const validateCode = (code: string, silent: boolean): number => {
        const validations: string[] = [];
        if (code === '') validations.push(translate('pim_table_attribute.validations.column_code_must_be_filled'));
        if (code !== '' && !/^[a-zA-Z0-9_]+$/.exec(code))
            validations.push(translate('pim_table_attribute.validations.invalid_code'));
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

    const validateDataType = (dataType: ColumnType | null, silent: boolean): number => {
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

    const handleCancel = ()=>{
        // In construction
        close();
    }

    const handleDelete = () => {
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
    };

    return (
        <Modal closeTitle={translate('pim_common.close')} onClose={close} illustration={<AttributesIllustration />}>
            <Modal.SectionTitle color='brand'>
                {translate('pim_table_attribute.form.attribute.table_attribute')}
            </Modal.SectionTitle>
            <Modal.Title>{/*translate('pim_table_attribute.form.attribute.delete_column')*/} !Confirm deletion</Modal.Title>
            <FieldsList>
                <div>
                    Are you sure you want to delete this column
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore
                    magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                    consequat.
                </div>
                <div>Please type !Column_code </div>
                <Field label={translate('pim_common.label')} locale={<LocaleLabel localeCode={catalogLocale} />}>
                    <TextInput value={columnDefinition.label} onChange={handleLabelChange} />
                </Field>
                <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
                    <TextInput
                        value={columnDefinition.code}
                        onChange={handleCodeChange}
                        maxLength={100}
                        characterLeftLabel={translate(
                            'pim_table_attribute.form.attribute.characters_left',
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
            </FieldsList>
            <Modal.BottomButtons>
                <Button level='primary' onClick={handleCancel} disabled={!isValid(true)}>
                    {/*translate('pim_common.create')*/}
                    {/*translate('pim_common.delete')*/}
                    Cancel
                </Button>
                <Button level='primary' onClick={handleDelete} disabled={!isValid(true)}>
                    {/*translate('pim_common.create')*/}
                    {/*translate('pim_common.delete')*/}
                    Delete
                </Button>
            </Modal.BottomButtons>
        </Modal>
    );
};

export {DeleteColumnModal};
