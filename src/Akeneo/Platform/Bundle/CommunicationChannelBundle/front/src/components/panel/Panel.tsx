import React from 'react';
import {useTranslate, useMediator} from '@akeneo-pim-community/legacy-bridge';
import {usePimVersion} from '../../hooks/usePimVersion';
import {HeaderPanel} from './Header';
import {ListAnnouncement} from './ListAnnouncement';
import {EmptyAnnouncementList} from './announcement';
import {formatCampaign} from '../../tools/formatCampaign';

const Panel = (): JSX.Element => {
  const __ = useTranslate();
  const mediator = useMediator();
  const pimVersion = usePimVersion();
  const cloudEEVersion = 'serenity';
  const campaign = null !== pimVersion.data ? formatCampaign(pimVersion.data.edition, pimVersion.data.version) : '';
  const isSerenity = null !== pimVersion.data && cloudEEVersion === pimVersion.data.edition.toLowerCase();

  const closePanel = () => {
    mediator.trigger('communication-channel:panel:close');
  };

  return (
    <>
      <HeaderPanel title={__('akeneo_communication_channel.panel.title')} onClickCloseButton={closePanel} />
      {isSerenity ? (
        <ListAnnouncement campaign={campaign} />
      ) : (
        <EmptyAnnouncementList text={__('akeneo_communication_channel.panel.list.empty')} />
      )}
    </>
  );
};

export {Panel};
