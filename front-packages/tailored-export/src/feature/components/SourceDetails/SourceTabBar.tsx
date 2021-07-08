import React, {useMemo} from 'react';
import {Pill, TabBar} from 'akeneo-design-system';
import {filterErrors, getLabel, useTranslate, useUserContext, ValidationError} from '@akeneo-pim-community/shared';
import {useAttributes} from '../../hooks';
import {Source} from '../../models';

type SourceTabBarProps = {
  sources: Source[];
  currentTab: string;
  validationErrors: ValidationError[];
  onTabChange: (newTab: string) => void;
};

const SourceTabBar = ({sources, currentTab, validationErrors, onTabChange}: SourceTabBarProps) => {
  const translate = useTranslate();
  const catalogLocale = useUserContext().get('catalogLocale');
  const attributeCodes = useMemo(
    () => sources.filter(({type}) => 'attribute' === type).map(({code}) => code),
    [sources]
  );
  const attributes = useAttributes(attributeCodes);

  return (
    <TabBar moreButtonTitle={translate('pim_common.more')} sticky={44}>
      {sources.map(source => (
        <TabBar.Tab key={source.uuid} isActive={currentTab === source.uuid} onClick={() => onTabChange(source.uuid)}>
          {'attribute' === source.type
            ? getLabel(
                attributes.find(attribute => attribute.code === source.code)?.labels ?? {},
                catalogLocale,
                source.code
              )
            : translate(`pim_common.${source.code}`)}
          {0 < filterErrors(validationErrors, `[${source.uuid}]`).length && <Pill level="danger" />}
        </TabBar.Tab>
      ))}
    </TabBar>
  );
};

export {SourceTabBar};
