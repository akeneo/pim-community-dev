import {Button, useBooleanState} from 'akeneo-design-system';
import {ManageOptionsModal} from '../ManageOptionsModal';
import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';
import {castSelectColumnDefinition, SelectOption} from '../../models';
import {ColumnProperties} from './index';

const ManageOptionsButtonContainer = styled.div`
  text-align: right;
`;

const SelectProperties: ColumnProperties = ({attribute, selectedColumn, handleChange}) => {
  const translate = useTranslate();
  const [isManageOptionsOpen, openManageOptions, closeManageOptions] = useBooleanState();

  const handleManageOptionChange = (options: SelectOption[]) => {
    castSelectColumnDefinition(selectedColumn).options = options;
    handleChange(selectedColumn);
  };

  return (
    <ManageOptionsButtonContainer>
      <Button onClick={openManageOptions} ghost size='small' level='tertiary'>
        {translate('pim_table_attribute.form.attribute.manage_options')}
      </Button>
      {isManageOptionsOpen && (
        <ManageOptionsModal
          attribute={attribute}
          columnDefinition={castSelectColumnDefinition(selectedColumn)}
          onClose={closeManageOptions}
          onChange={handleManageOptionChange}
        />
      )}
    </ManageOptionsButtonContainer>
  );
};

export default SelectProperties;
