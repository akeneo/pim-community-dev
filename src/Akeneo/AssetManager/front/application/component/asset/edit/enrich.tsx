import React from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {getColor, SectionTitle} from 'akeneo-design-system';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import {assetValueUpdated, saveAsset} from 'akeneoassetmanager/application/action/asset/edit';
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset/edit/form';
import {denormalizeChannelReference} from 'akeneoassetmanager/domain/model/channel-reference';
import {ValueCollection} from 'akeneoassetmanager/application/component/asset/edit/enrich/value';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {canEditAssetFamily, canEditLocale} from 'akeneoassetmanager/application/reducer/right';
import {denormalizeLocaleReference} from 'akeneoassetmanager/domain/model/locale-reference';
import LinkedProducts from 'akeneoassetmanager/application/component/asset/edit/linked-products';
import {Subsection} from 'akeneoassetmanager/application/component/app/subsection';
import {MainMediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/main-media-preview';
import {useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.div`
  display: flex;
  flex-direction: row;
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
      onSubmit: () => void;
    };
  };
};

const Enrich = ({form, context, events, canEditCurrentLocale, canEditCurrentFamily}: StateProps & DispatchProps) => {
  const translate = useTranslate();
  const {isGranted} = useSecurity();
  const asset = form.data;

  return (
    <Container>
      <LeftColumn>
        <Subsection>
          <SectionTitle sticky={192}>
            <SectionTitle.Title>{translate('pim_asset_manager.asset.enrich.edit_subsection')}</SectionTitle.Title>
          </SectionTitle>
          <ValueCollection
            asset={asset}
            channel={denormalizeChannelReference(context.channel)}
            locale={denormalizeLocaleReference(context.locale)}
            errors={form.errors}
            onValueChange={events.form.onValueChange}
            onFieldSubmit={events.form.onSubmit}
            canEditLocale={canEditCurrentLocale}
            canEditAsset={canEditCurrentFamily && isGranted('akeneo_assetmanager_asset_edit')}
          />
        </Subsection>
      </LeftColumn>
      <Separator />
      <RightColumn>
        <MainMediaPreview asset={asset} context={context} />
        <LinkedProducts />
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
          onSubmit: () => {
            dispatch(saveAsset());
          },
        },
      },
    };
  }
)(Enrich);
