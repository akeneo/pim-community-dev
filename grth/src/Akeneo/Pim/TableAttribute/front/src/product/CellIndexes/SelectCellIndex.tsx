import React from 'react';
import {TableInput} from 'akeneo-design-system';
import {useFetchOptions} from '../useFetchOptions';
import {SelectOptionCode} from '../../models';
import {useAttributeContext} from '../../contexts';
import {LoadingPlaceholderContainer} from '../../shared';
import {useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {cellMatchers} from '../CellMatchers';

const FirstCellLoadingPlaceholderContainer = styled(LoadingPlaceholderContainer)`
  padding-top: 10px;
  & > * {
    height: 20px;
  }
`;

type SelectCellIndexProps = {
  searchText: string;
  isInErrorFromBackend: boolean;
  value: SelectOptionCode;
};

const SelectCellIndex: React.FC<SelectCellIndexProps> = ({searchText, isInErrorFromBackend, value}) => {
  const translate = useTranslate();
  const {attribute, setAttribute} = useAttributeContext();
  const firstColumn = attribute?.table_configuration?.[0];
  const {getOptionLabel} = useFetchOptions(attribute, setAttribute);
  const isMatching = cellMatchers.select();

  return (
    <>
      {firstColumn && (
        <TableInput.CellContent
          rowTitle={true}
          highlighted={isMatching(value, searchText, firstColumn.code)}
          inError={isInErrorFromBackend || getOptionLabel(firstColumn.code, value) === null}
        >
          {typeof getOptionLabel(firstColumn.code, value) === 'undefined' ? (
            <FirstCellLoadingPlaceholderContainer>
              <div>{translate('pim_common.loading')}</div>
            </FirstCellLoadingPlaceholderContainer>
          ) : (
            getOptionLabel(firstColumn.code, value) || `[${value}]`
          )}
        </TableInput.CellContent>
      )}
    </>
  );
};

export {SelectCellIndex};
