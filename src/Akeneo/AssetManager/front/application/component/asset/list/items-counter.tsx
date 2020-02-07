import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

const StyledItemsCounter = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
  font-weight: normal;
  position: relative;
  text-transform: none;
`;

export const ItemsCounter = React.memo(({count}: {count: number}) => {
  return (
    <StyledItemsCounter>
      {__(
        'pim_asset_manager.result_counter',
        {
          count: count,
        },
        count
      )}
    </StyledItemsCounter>
  );
});
