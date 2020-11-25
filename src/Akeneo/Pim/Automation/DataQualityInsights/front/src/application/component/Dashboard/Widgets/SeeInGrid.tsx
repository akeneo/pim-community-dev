import React, {FC} from 'react';
import {Button} from 'akeneo-design-system';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type Props = {
  follow: () => void;
};

const CustomButton = styled(Button)`
  white-space: nowrap;
`;
const SeeInGrid: FC<Props> = ({follow}) => {
  const translate = useTranslate();

  return (
    <CustomButton ghost level={'tertiary'} size={'small'} onClick={follow}>
      {translate('akeneo_data_quality_insights.dqi_dashboard.widgets.see_in_grid')}
    </CustomButton>
  );
};

export {SeeInGrid};
