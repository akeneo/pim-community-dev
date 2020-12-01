import $ from 'jquery';
import * as React from 'react';
import * as ReactDOM from 'react-dom';
import AssetSelector from 'akeneoassetmanager/application/component/app/asset-selector';
import AssetSelectorLegacyHelper from 'akeneopimenrichmentassetmanager/product/field/asset-collection/asset-selector-legacy-helper';
import AssetCode, {assetCodeStringValue, denormalizeAssetCode} from 'akeneoassetmanager/domain/model/asset/code';
import {denormalizeChannelReference} from 'akeneoassetmanager/domain/model/channel-reference';
import __ from 'akeneoassetmanager/tools/translator';
import {denormalizeLocaleReference} from 'akeneoassetmanager/domain/model/locale-reference';
import {denormalizeAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {getMissingRequiredFields} from 'pimui/js/provider/to-fill-field-provider';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

const Field = require('pim/field');
const UserContext = require('pim/user-context');

/**
 * Asset family collection field for attribute form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetCollectionField extends (Field as {new (config: any): any}) {
  constructor(config: any) {
    super(config);

    this.fieldType = 'pim-asset-collection-field';
  }

  renderInput(templateContext: any) {
    if (this.isMassEdit(templateContext)) {
      return this.renderLegacyInput(templateContext);
    }

    return this.renderIsDisabledMessage(templateContext);
  }

  renderIsDisabledMessage(templateContext: any): HTMLElement {
    const container = document.createElement('div');
    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <AssetSelectorLegacyHelper
            label={templateContext.label}
            isMissingRequired={this.isMissingRequired(templateContext)}
          />
        </ThemeProvider>
      </DependenciesProvider>,
      container
    );
    return container;
  }

  renderLegacyInput(templateContext: any): HTMLElement {
    const container = document.createElement('div');
    ReactDOM.render(
      <AssetSelector
        assetFamilyIdentifier={denormalizeAssetFamilyIdentifier(templateContext.attribute.reference_data_name)}
        value={templateContext.value.data.map((assetCode: string) => denormalizeAssetCode(assetCode))}
        locale={denormalizeLocaleReference(UserContext.get('catalogLocale'))}
        channel={denormalizeChannelReference(UserContext.get('catalogScope'))}
        multiple={true}
        readOnly={'view' === templateContext.editMode}
        placeholder={__('pim_asset_manager.asset.selector.no_value')}
        onChange={(assetCodes: AssetCode[]) => {
          this.errors = [];
          this.setCurrentValue(assetCodes.map((assetCode: AssetCode) => assetCodeStringValue(assetCode)));
          this.render();
        }}
      />,
      container
    );
    return container;
  }

  isMassEdit(templateContext: any): boolean {
    // This should be temporary, we currently don't have an easy way to know the real context
    return templateContext.context.entity.created === undefined;
  }

  isMissingRequired(templateContext: any): boolean {
    const scope = UserContext.get('catalogScope');
    const locale = UserContext.get('catalogLocale');
    const product = templateContext.context.entity;

    const requiredFields = getMissingRequiredFields(product, scope, locale);

    if (0 === requiredFields.length) {
      return false;
    }

    return requiredFields.indexOf(templateContext.attribute.code) !== -1;
  }

  renderCopyInput() {
    return null;
  }

  getFieldValue(field: any) {
    const value = $(field).val();

    return null === value ? [] : value;
  }
}

module.exports = AssetCollectionField;
