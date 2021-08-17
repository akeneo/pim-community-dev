import React, {useRef} from 'react';
import styled from 'styled-components';
import {CloseIcon, IconButton, Table, TextInput, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ConcatElement} from '../../../../models';

const RemoveCell = styled(Table.Cell)`
  width: 50px;
`;

type StringRowProps = {
  element: ConcatElement;
  onConcatElementChange: (element: ConcatElement) => void;
  onConcatElementRemove: (elementUuid: string) => void;
};

const StringRow = ({element, onConcatElementChange, onConcatElementRemove, ...rest}: StringRowProps) => {
  const translate = useTranslate();
  const inputRef = useRef<HTMLInputElement>(null);

  const handleChange = (value: string) => onConcatElementChange({...element, value});
  const handleRemove = () => onConcatElementRemove(element.uuid);
  const handleBlur = () => '' === element.value && handleRemove();

  useAutoFocus(inputRef);

  return (
    <Table.Row {...rest}>
      <Table.Cell>
        <TextInput
          ref={inputRef}
          placeholder={translate('akeneo.tailored_export.column_details.concatenation.text_placeholder')}
          value={element.value}
          onChange={handleChange}
          onBlur={handleBlur}
        />
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

export {StringRow};
