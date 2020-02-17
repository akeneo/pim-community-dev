import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import {assetValueUpdated, saveAsset} from 'akeneoassetmanager/application/action/asset/edit';
import {EditionFormState} from 'akeneoassetmanager/application/reducer/asset/edit/form';
import {denormalizeChannelReference} from 'akeneoassetmanager/domain/model/channel-reference';
import renderValues from 'akeneoassetmanager/application/component/asset/edit/enrich/value';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import Key from 'akeneoassetmanager/tools/key';
import {canEditAssetFamily, canEditLocale} from 'akeneoassetmanager/application/reducer/right';
import {denormalizeLocaleReference} from 'akeneoassetmanager/domain/model/locale-reference';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoassetmanager/tools/translator';
import LinkedProducts from 'akeneoassetmanager/application/component/asset/edit/linked-products';
import {Subsection, SubsectionHeader} from 'akeneoassetmanager/application/component/app/subsection';

const securityContext = require('pim/security-context');

const Container = styled.div`
  display: flex;
  flex-direction: row;
`;

const LeftColumn = styled.div`
  flex-grow: 0;
  flex-shrink: 0;
  width: 600px;
`;

const Separator = styled.div`
  background: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  flex-shrink: 0;
  margin: 0 40px;
  width: 1px;
`;

const RightColumn = styled.div`
  flex-grow: 1;
`;

interface StateProps {
  form: EditionFormState;
  context: {
    locale: string;
    channel: string;
  };
  rights: {
    locale: {
      edit: boolean;
    };
    asset: {
      edit: boolean;
      delete: boolean;
    };
  };
}

interface DispatchProps {
  events: {
    form: {
      onValueChange: (value: EditionValue) => void;
      onSubmit: () => void;
    };
  };
}

class Enrich extends React.Component<StateProps & DispatchProps> {
  private labelInput: HTMLInputElement;
  props: StateProps & DispatchProps;

  componentDidMount() {
    if (this.labelInput) {
      this.labelInput.focus();
    }
  }

  keyDown = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (Key.Enter === event.key) this.props.events.form.onSubmit();
  };

  render() {
    const asset = this.props.form.data;

    return (
      <Container>
        <LeftColumn>
          <Subsection>
            <SubsectionHeader>
              <span>{__('pim_asset_manager.asset.enrich.edit_subsection')}</span>
            </SubsectionHeader>
            <div className="AknFormContainer AknFormContainer--wide AknFormContainer--withPadding">
              {renderValues(
                asset,
                denormalizeChannelReference(this.props.context.channel),
                denormalizeLocaleReference(this.props.context.locale),
                this.props.form.errors,
                this.props.events.form.onValueChange,
                this.props.events.form.onSubmit,
                this.props.rights
              )}
            </div>
          </Subsection>
        </LeftColumn>
        <Separator />
        <RightColumn>
          <LinkedProducts />
        </RightColumn>
      </Container>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const locale = state.user.catalogLocale;

    return {
      form: state.form,
      context: {
        locale: locale,
        channel: state.user.catalogChannel,
      },
      rights: {
        locale: {
          edit: canEditLocale(state.right.locale, locale),
        },
        asset: {
          edit:
            securityContext.isGranted('akeneo_assetmanager_asset_edit') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.assetFamily.identifier),
          delete:
            securityContext.isGranted('akeneo_assetmanager_asset_edit') &&
            securityContext.isGranted('akeneo_assetmanager_asset_delete') &&
            canEditAssetFamily(state.right.assetFamily, state.form.data.assetFamily.identifier),
        },
      },
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
