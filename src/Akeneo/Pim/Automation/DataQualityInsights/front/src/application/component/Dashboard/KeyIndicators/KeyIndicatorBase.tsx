import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ProgressBar} from 'akeneo-design-system';
import {IntegerPercent} from '../../../../domain';
import {getProgressBarLevel} from '../../../helper/Dashboard/KeyIndicator';
import {Container, Content, Icon} from './styled';

interface Props {
  percentOK: IntegerPercent;
  titleI18nKey: string;
  icon: React.ReactNode;
}

export const KeyIndicatorBase: FC<Props> = ({children, icon, percentOK, titleI18nKey}) => {
  const translate = useTranslate();
  return (
    <Container>
      <Icon>{icon}</Icon>
      <Content>
        <ProgressBar
          level={getProgressBarLevel(percentOK)}
          light={percentOK === 0 || (percentOK >= 50 && percentOK < 80)}
          percent={percentOK}
          progressLabel={percentOK + '%'}
          size="small"
          title={translate(titleI18nKey)}
        />
        {children}
      </Content>
    </Container>
  );
};
