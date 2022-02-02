import React from 'react';
import {connect, useDispatch} from 'react-redux';
import styled from 'styled-components';
import {getColor, SectionTitle} from 'akeneo-design-system';
import {Section, useSecurity, useTranslate} from '@akeneo-pim-community/shared';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import {assetValueUpdated, saveAsset} from 'akeneoassetmanager/application/action/asset/edit';
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset/edit/form';
import {denormalizeChannelReference} from 'akeneoassetmanager/domain/model/channel-reference';
import {ValueCollection} from 'akeneoassetmanager/application/component/asset/edit/enrich/value';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {canEditAssetFamily, canEditLocale} from 'akeneoassetmanager/application/reducer/right';
import {denormalizeLocaleReference} from 'akeneoassetmanager/domain/model/locale-reference';
import {LinkedProducts} from 'akeneoassetmanager/application/component/asset/edit/linked-products';
import {MainMediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/main-media-preview';
import {useAssetFetcher} from 'akeneoassetmanager/infrastructure/fetcher/useAssetFetcher';

const Container = styled.div`
  display: flex;
  flex-direction: row;
  padding: 0 40px;
`;

const LeftColumn = styled.div`
  flex-grow: 0;
  flex-shrink: 0;
  width: calc(50% - 40px);
  min-width: 460px;
`;

const Separator = styled.div`
  background: ${getColor('brand', 100)};
  flex-shrink: 0;
  margin: 0 40px;
  width: 1px;
`;

const RightColumn = styled.div`
  flex-grow: 1;
`;

type StateProps = {
  form: EditionFormState;
  context: {
    locale: string;
    channel: string;
  };
  canEditCurrentLocale: boolean;
  canEditCurrentFamily: boolean;
};

type DispatchProps = {
  events: {
    form: {
      onValueChange: (value: EditionValue) => void;
    };
  };
};

const Enrich = ({form, context, events, canEditCurrentLocale, canEditCurrentFamily}: StateProps & DispatchProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const dispatch = useDispatch();
  const assetFetcher = useAssetFetcher();
  const asset = form.data;

  return (
    <Container>
      <LeftColumn>
        <Section>
          <SectionTitle sticky={202}>
            <SectionTitle.Title>{translate('pim_asset_manager.asset.enrich.edit_subsection')}</SectionTitle.Title>
          </SectionTitle>
          <ValueCollection
            asset={asset}
            channel={denormalizeChannelReference(context.channel)}
            locale={denormalizeLocaleReference(context.locale)}
            errors={form.errors}
            onValueChange={events.form.onValueChange}
            onFieldSubmit={() => dispatch(saveAsset(assetFetcher))}
            canEditLocale={canEditCurrentLocale}
            canEditAsset={canEditCurrentFamily && isGranted('akeneo_assetmanager_asset_edit')}
          />
        </Section>
      </LeftColumn>
      <Separator />
      <RightColumn>
        <MainMediaPreview asset={asset} context={context} />
        <LinkedProducts assetFamilyIdentifier={asset.assetFamily.identifier} assetCode={asset.code} />
      </RightColumn>
    </Container>
  );
};

export default connect(
  (state: EditState): StateProps => {
    const locale = state.user.catalogLocale;

    return {
      form: state.form,
      context: {
        locale: locale,
        channel: state.user.catalogChannel,
      },
      canEditCurrentLocale: canEditLocale(state.right.locale, locale),
      canEditCurrentFamily: canEditAssetFamily(state.right.assetFamily, state.form.data.assetFamily.identifier),
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        form: {
          onValueChange: (value: EditionValue) => {
            dispatch(assetValueUpdated(value));
          },
        },
      },
    };
  }
)(Enrich);
