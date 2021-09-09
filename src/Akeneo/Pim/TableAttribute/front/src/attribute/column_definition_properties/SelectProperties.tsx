import {ColumnProperties} from '../TableStructureApp';
import {Button, useBooleanState} from 'akeneo-design-system';
import {ManageOptionsModal} from '../ManageOptionsModal';
import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {SelectColumnDefinition, SelectOption} from '../../models';

const ManageOptionsButtonContainer = styled.div`
  text-align: right;
`;

const SelectProperties: ColumnProperties = ({attribute, selectedColumn, handleChange}) => {
  const translate = useTranslate();
  const [isManageOptionsOpen, openManageOption, closeManageOption] = useBooleanState();

  const handleManageOptionChange = (options: SelectOption[]) => {
    (selectedColumn as SelectColumnDefinition).options = options;
    handleChange(selectedColumn);
  };

  return (
    <ManageOptionsButtonContainer>
      <Button onClick={openManageOption} ghost size='small' level='tertiary'>
        {translate('pim_table_attribute.form.attribute.manage_options')}
      </Button>
      {isManageOptionsOpen && (
        <ManageOptionsModal
          attribute={attribute}
          columnDefinition={selectedColumn as SelectColumnDefinition}
          onClose={closeManageOption}
          onChange={handleManageOptionChange}
        />
      )}
    </ManageOptionsButtonContainer>
  );
};

export default SelectProperties;
