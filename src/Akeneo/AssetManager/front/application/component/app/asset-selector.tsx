import * as React from 'react';
import * as $ from 'jquery';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
const routing = require('routing');
import {NormalizedAsset, NormalizedItemAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference, {channelReferenceStringValue} from 'akeneoassetmanager/domain/model/channel-reference';
import {getLabel} from 'pimui/js/i18n';
import __ from 'akeneoassetmanager/tools/translator';

const renderRow = (label: string, normalizedAsset: NormalizedItemAsset, withLink: boolean, compact: boolean) => {
  return `
  <img width="34" height="34" src="${normalizedAsset.image}" style="object-fit: cover;"/>
  <span class="select2-result-label-main">
    <span class="select2-result-label-top">
      ${normalizedAsset.code}
    </span>
    <span class="select2-result-label-bottom">${label}</span>
  </span>
  ${
    withLink && !compact
      ? `<a
      class="select2-result-label-link AknIconButton AknIconButton--small AknIconButton--link"
      data-asset-family-identifier="${normalizedAsset.asset_family_identifier}"
      data-asset-code="${normalizedAsset.code}"
      target="_blank"
      href="#${routing.generate('akeneo_asset_manager_asset_edit', {
        assetFamilyIdentifier: normalizedAsset.asset_family_identifier,
        assetCode: normalizedAsset.code,
        tab: 'enrich',
      })}"></a>`
      : ''
  }`;
};

export type AssetSelectorProps = {
  value: AssetCode[] | AssetCode | null;
  assetFamilyIdentifier: AssetFamilyIdentifier;
  multiple?: boolean;
  readOnly?: boolean;
  compact?: boolean;
  id?: string;
  locale: LocaleReference;
  channel: ChannelReference;
  placeholder?: string;
  onChange: (value: AssetCode[] | AssetCode | null) => void;
};

type Select2Item = {id: string; text: string; original: NormalizedItemAsset};

export default class AssetSelector extends React.Component<AssetSelectorProps> {
  PAGE_SIZE = 200;
  static defaultProps = {
    multiple: false,
    readOnly: false,
    compact: false,
  };
  private DOMel: React.RefObject<HTMLInputElement>;
  private el: any;

  constructor(props: AssetSelectorProps) {
    super(props);

    this.DOMel = React.createRef();
  }

  formatItem(normalizedAsset: NormalizedItemAsset): Select2Item {
    return {
      id: normalizedAsset.code,
      text: getLabel(normalizedAsset.labels, localeReferenceStringValue(this.props.locale), normalizedAsset.code),
      original: normalizedAsset,
    };
  }

  getSelectedAssetCode(value: null | AssetCode[] | AssetCode, multiple: boolean) {
    if (multiple) {
      return (value as AssetCode[]).map((assetCode: AssetCode) => assetCode.stringValue());
    } else {
      return null === value ? [] : [(value as AssetCode).stringValue()];
    }
  }

  componentDidMount() {
    if (null === this.DOMel.current) {
      return;
    }

    this.el = $(this.DOMel.current);

    if (undefined !== this.el.select2) {
      const containerCssClass = `asset-selector ${this.props.readOnly ? 'asset-selector--disabled' : ''} ${
        this.props.compact ? 'asset-selector--compact' : ''
      }`;
      const dropdownCssClass = `${this.props.multiple ? 'asset-selector-multi-dropdown' : 'asset-selector-dropdown'} ${
        this.props.compact ? 'asset-selector-dropdown--compact' : ''
      }`;

      this.el.select2({
        allowClear: true,
        placeholder: this.props.placeholder,
        placeholderOption: '',
        multiple: this.props.multiple,
        dropdownCssClass,
        containerCssClass,
        ajax: {
          url: routing.generate('akeneo_asset_manager_asset_index_rest', {
            assetFamilyIdentifier: this.props.assetFamilyIdentifier.stringValue(),
          }),
          quietMillis: 250,
          cache: true,
          type: 'PUT',
          params: {contentType: 'application/json;charset=utf-8'},
          data: (term: string, page: number): string => {
            const selectedAssets = this.getSelectedAssetCode(this.props.value, this.props.multiple as boolean);
            const searchQuery = {
              channel: channelReferenceStringValue(this.props.channel),
              locale: localeReferenceStringValue(this.props.locale),
              size: this.PAGE_SIZE,
              page: page - 1,
              filters: [
                {
                  field: 'asset_family',
                  operator: '=',
                  value: this.props.assetFamilyIdentifier.stringValue(),
                },
                {
                  field: 'code_label',
                  operator: '=',
                  value: term,
                },
                {
                  field: 'code',
                  operator: 'NOT IN',
                  value: selectedAssets,
                },
              ],
            };

            return JSON.stringify(searchQuery);
          },
          results: (result: {items: NormalizedAsset[]; matchesCount: number}) => {
            const items = result.items.map(this.formatItem.bind(this));

            return {
              more: this.PAGE_SIZE === items.length,
              results: items,
            };
          },
        },
        initSelection: async (element: any, callback: (item: Select2Item | Select2Item[]) => void) => {
          if (this.props.multiple) {
            const initialAssetCodes = element
              .val()
              .split(',')
              .map((assetCode: string) => AssetCode.create(assetCode));
            const result = await assetFetcher.fetchByCodes(
              this.props.assetFamilyIdentifier,
              initialAssetCodes,
              {
                channel: channelReferenceStringValue(this.props.channel),
                locale: localeReferenceStringValue(this.props.locale),
              },
              true
            );

            callback(result.map(this.formatItem.bind(this)));
          } else {
            const initialValue = element.val();
            assetFetcher
              .fetchByCodes(this.props.assetFamilyIdentifier, [AssetCode.create(initialValue)], {
                channel: channelReferenceStringValue(this.props.channel),
                locale: localeReferenceStringValue(this.props.locale),
              })
              .then((assets: NormalizedItemAsset[]) => {
                callback(this.formatItem(assets[0]));
              });
          }
        },
        formatSelection: (asset: Select2Item, container: any) => {
          if (Array.isArray(asset) && 0 === asset.length) {
            return;
          }
          container
            .addClass('select2-search-choice-value')
            .append(
              $(
                renderRow(
                  asset.text,
                  asset.original,
                  false,
                  undefined === this.props.compact ? false : this.props.compact
                )
              )
            );
        },
        formatResult: (asset: Select2Item, container: any) => {
          container
            .addClass('select2-search-choice-value')
            .append(
              $(
                renderRow(
                  asset.text,
                  asset.original,
                  false,
                  undefined === this.props.compact ? false : this.props.compact
                )
              )
            );
        },
      });

      this.el.on('change', (event: any) => {
        const newValue = this.props.multiple
          ? event.val.map((assetCode: string) => AssetCode.create(assetCode))
          : '' === event.val
          ? null
          : AssetCode.create(event.val);
        this.props.onChange(newValue);
      });

      // Prevent the onSelect event to apply it even when the options are null
      const select2 = this.el.data('select2');
      select2.onSelect = (function(fn) {
        return function(_data: any, options: any) {
          if (null === options || 'A' !== options.target.nodeName) {
            //@ts-ignore Dirty but comming from select2...
            fn.apply(this, arguments);
          }
        };
      })(select2.onSelect);
    } else {
      this.el.prop('type', 'text');
    }
  }

  componentWillUnmount() {
    this.el.off('change');
  }

  componentDidUpdate(prevProps: AssetSelectorProps) {
    if (this.props.value !== prevProps.value) {
      this.el.val(this.normalizeValue(this.props.value)).trigger('change.select2');
    }
  }

  normalizeValue(value: AssetCode[] | AssetCode | null): string {
    if (null === value) {
      return '';
    }

    return this.props.multiple
      ? (value as AssetCode[]).map((assetCode: AssetCode) => assetCode.stringValue()).join(',')
      : (value as AssetCode).stringValue();
  }

  render(): JSX.Element | JSX.Element[] {
    const {assetFamilyIdentifier, compact, ...props} = this.props;
    const className = `asset-selector ${this.props.readOnly ? 'asset-selector--disabled' : ''} ${
      compact ? 'asset-selector--compact' : ''
    }`;

    return (
      <div className="asset-selector-container">
        <input
          ref={this.DOMel}
          className={className}
          {...props}
          type="hidden"
          value={this.normalizeValue(this.props.value)}
          disabled={this.props.readOnly}
          onChange={(event: any) => {
            const newValue = this.props.multiple
              ? event.target.value.split(',').map((assetCode: string) => AssetCode.create(assetCode))
              : '' === event.target.value
              ? null
              : AssetCode.create(event.target.value);
            this.props.onChange(newValue);
          }}
        />
      </div>
    );
  }
}
