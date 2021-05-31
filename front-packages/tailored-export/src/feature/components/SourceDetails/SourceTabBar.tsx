import React, {useMemo} from 'react';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {TabBar} from 'akeneo-design-system';
import {Source} from '../../models';
import {useAttributes} from '../../hooks';

type SourceTabBarProps = {
  sources: Source[];
  currentTab: string;
  onTabChange: (newTab: string) => void;
};

const SourceTabBar = ({sources, currentTab, onTabChange}: SourceTabBarProps) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const attributeCodes = useMemo(() => sources.map(source => source.code), [sources]);
  const attributes = useAttributes(attributeCodes);

  return (
    <TabBar moreButtonTitle={translate('pim_common.more')} sticky={44}>
      {sources.map(source => (
        <TabBar.Tab key={source.uuid} isActive={currentTab === source.uuid} onClick={() => onTabChange(source.uuid)}>
          {source.type === 'attribute'
            ? getLabel(
                attributes.find(attribute => attribute.code === source.code)?.labels ?? {},
                userContext.get('catalogLocale'),
                source.code
              )
            : translate(`pim_common.${source.code}`)}
        </TabBar.Tab>
      ))}
    </TabBar>
  );
};

export {SourceTabBar};
