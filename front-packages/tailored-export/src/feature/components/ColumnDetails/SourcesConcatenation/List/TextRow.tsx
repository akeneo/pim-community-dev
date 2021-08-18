import React, {useRef} from 'react';
import styled from 'styled-components';
import {CloseIcon, Helper, IconButton, Table, TextInput, useAutoFocus} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {ConcatElement} from '../../../../models';

const RemoveCell = styled(Table.Cell)`
  width: 50px;
`;

const Field = styled.div`
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

type TextRowProps = {
  validationErrors: ValidationError[];
  concatElement: ConcatElement;
  onConcatElementChange: (concatElement: ConcatElement) => void;
  onConcatElementRemove: (concatElementUuid: string) => void;
};

const TextRow = ({
  validationErrors,
  concatElement,
  onConcatElementChange,
  onConcatElementRemove,
  ...rest
}: TextRowProps) => {
  const translate = useTranslate();
  const inputRef = useRef<HTMLInputElement>(null);
  const valueErrors = filterErrors(validationErrors, '[value]');

  const handleChange = (value: string) => onConcatElementChange({...concatElement, value});
  const handleRemove = () => onConcatElementRemove(concatElement.uuid);
  const handleBlur = () => '' === concatElement.value && handleRemove();

  useAutoFocus(inputRef);

  return (
    <Table.Row {...rest}>
      <Table.Cell>
        <Field>
          <TextInput
            ref={inputRef}
            placeholder={translate('akeneo.tailored_export.column_details.concatenation.text_placeholder')}
            value={concatElement.value}
            onChange={handleChange}
            onBlur={handleBlur}
          />
          {valueErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
      </Table.Cell>
      <RemoveCell>
        <IconButton
          ghost="borderless"
          level="tertiary"
          title={translate('pim_common.remove')}
          icon={<CloseIcon />}
          onClick={handleRemove}
        />
      </RemoveCell>
    </Table.Row>
  );
};

export {TextRow};
