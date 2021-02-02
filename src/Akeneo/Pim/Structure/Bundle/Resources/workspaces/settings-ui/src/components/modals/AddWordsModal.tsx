import React, {FC, useState} from 'react';
import {Button, Field, LocaleIllustration, Modal, SectionTitle, TagInput, Title, getColor} from 'akeneo-design-system';
import {NotificationLevel, useNotify, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {useLocalesGridDictionariesState} from '../../hooks';
import {useLocaleSelection} from '../../hooks/locales/useLocaleSelection';

type AddWordsModalProps = {
  localesCount: number;
  closeModal: () => void;
};

const AddWordsModal: FC<AddWordsModalProps> = ({localesCount, closeModal}) => {
  const translate = useTranslate();
  const [words, setWords] = useState<string[]>([]);
  const {addWordsToDictionaries, refreshDictionaryInfo, localesDictionaryInfo} = useLocalesGridDictionariesState();
  const notify = useNotify();
  const {selectedLocales} = useLocaleSelection();

  const addWordsToSelectedLocales = async () => {
    if (words.length === 0) {
      return;
    }

    const activatedLocaleCodes = Object.entries(localesDictionaryInfo)
      .filter(([_, totalWords]) => totalWords !== null)
      .map(([localeCode]) => localeCode);

    if (activatedLocaleCodes.filter(localeCode => selectedLocales.includes(localeCode)).length === 0) {
      closeModal();
      notify(
        NotificationLevel.ERROR,
        translate('pimee_enrich.entity.locale.grid.modal.no_supported_locales.title'),
        // @ts-ignore
        <NotificationContent
          dangerouslySetInnerHTML={{
            __html: translate('pimee_enrich.entity.locale.grid.modal.no_supported_locales.content', {
              link: 'https://help.akeneo.com/pim/serenity/articles/improve-data-quality.html#improve-data-quality',
            }),
          }}
        />
      );
      return;
    }

    await addWordsToDictionaries(words);
    closeModal();
    notify(
      NotificationLevel.SUCCESS,
      translate('pimee_enrich.entity.locale.grid.modal.locales_added_notification.content'),
      // @ts-ignore @fixme: homogeinize notification interfaces between messenger.tsx and DependenciesProvider.type.ts
      {
        titleMessage: translate('pimee_enrich.entity.locale.grid.modal.locales_added_notification.title', {
          count: localesCount.toString(),
        }),
      }
    );
    await refreshDictionaryInfo();
  };

  return (
    <Modal closeTitle="Close" onClose={closeModal} illustration={<LocaleIllustration />}>
      <Modal.TopRightButtons>
        <Button level="primary" onClick={addWordsToSelectedLocales}>
          {translate('pim_common.save')}
        </Button>
      </Modal.TopRightButtons>
      <SectionTitle color="brand">{translate('pim_enrich.entity.locale.plural_label')}</SectionTitle>
      <Title>{translate('pimee_enrich.entity.locale.grid.modal.title', {count: localesCount.toString()})}</Title>

      <Subtitle>{translate('pimee_enrich.entity.locale.grid.modal.subtitle')}</Subtitle>

      <Field label={translate('pimee_enrich.entity.locale.grid.add_words')}>
        <TagInput value={words} onChange={setWords} />
      </Field>
    </Modal>
  );
};

const NotificationContent = styled.span`
  a {
    color: ${getColor('grey', 140)};
    text-decoration: underline;
  }
`;

const Subtitle = styled.div`
  font-size: 15px;
  margin-bottom: 25px;
`;

export {AddWordsModal};
