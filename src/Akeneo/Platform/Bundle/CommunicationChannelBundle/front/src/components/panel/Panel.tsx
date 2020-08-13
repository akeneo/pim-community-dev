import React, {useState, useEffect} from 'react';
import {useTranslate, useMediator} from '@akeneo-pim-community/legacy-bridge';
import {usePimVersion} from '../../hooks/usePimVersion';
import {HeaderPanel} from './Header';
import {AnnouncementList} from './AnnouncementList';
import {EmptyAnnouncementList} from './announcement';
import {formatCampaign} from '../../tools/formatCampaign';

const Panel = (): JSX.Element => {
  const __ = useTranslate();
  const mediator = useMediator();
  const [isOpened, setIsOpened] = useState<boolean>(false);
  const [campaign, setCampaign] = useState<string>('');
  const pimVersion = usePimVersion();

  const onClosePanel = () => {
    mediator.trigger('communication-channel:panel:close');
  };

  useEffect(() => {
      /* istanbul ignore next: can't test the callback function */
      mediator.on('communication-channel:panel:open', () => {
        setIsOpened(true);
      });
      /* istanbul ignore next */
      mediator.on('communication-channel:panel:close', () => {
        setIsOpened(false);
      });
  }, []);

  useEffect(() => {
    if (null !== pimVersion.data) {
      setCampaign(formatCampaign(pimVersion.data.edition, pimVersion.data.version));
    }
  }, [pimVersion.data]);

  if (pimVersion.hasError) {
    return (
      <>
        <HeaderPanel title={__('akeneo_communication_channel.panel.title')} onClickCloseButton={onClosePanel} />
        <EmptyAnnouncementList text={__('akeneo_communication_channel.panel.list.error')} />
      </>
    );
  }

  return (
    <>
      <HeaderPanel title={__('akeneo_communication_channel.panel.title')} onClickCloseButton={onClosePanel} />
        <AnnouncementList campaign={campaign} panelIsClosed={!isOpened} />
    </>
  );
};

export {Panel};
