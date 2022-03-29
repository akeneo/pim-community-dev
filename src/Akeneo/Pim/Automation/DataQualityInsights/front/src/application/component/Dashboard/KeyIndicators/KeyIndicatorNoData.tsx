import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ProgressBar} from 'akeneo-design-system';

import {Container, Content, Icon, Text} from './styled';

interface Props {
  type: string;
  title: string;
}

export const KeyIndicatorNoData: FC<Props> = ({type, title, children}) => {
  const translate = useTranslate();
  return (
    <Container>
      <Icon>{children}</Icon>
      <Content>
        <ProgressBar
          size="small"
          title={translate(title)}
          progressLabel={'\u00a0'}
          light={true}
          level={'tertiary'}
          percent={0}
        />
        <Text>{translate(`akeneo_data_quality_insights.dqi_dashboard.key_indicators.list.${type}.no_data`)}</Text>
      </Content>
    </Container>
  );
};
