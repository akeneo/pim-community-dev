import * as React from 'react';
import {FilterView, FilterViewProps} from 'akeneoassetmanager/application/configuration/value';
import {ConcreteAssetAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {connect} from 'react-redux';
import __ from 'akeneoassetmanager/tools/translator';
import {ConcreteAssetCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset-collection';
import AssetSelector from 'akeneoassetmanager/application/component/app/asset-selector';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {denormalizeLocaleReference} from 'akeneoassetmanager/domain/model/locale-reference';
import {denormalizeChannelReference} from 'akeneoassetmanager/domain/model/channel-reference';
import assetFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset';
import {NormalizedAsset} from 'akeneoassetmanager/domain/model/asset/asset';
import {getLabel} from 'pimui/js/i18n';
import {getAttributeFilterKey} from 'akeneoassetmanager/tools/filter';

const memo = (React as any).memo;
const useState = (React as any).useState;
const useEffect = (React as any).useEffect;

type AssetFilterViewProps = FilterViewProps & {
  context: {
    locale: string;
    channel: string;
  };
};

const DEFAULT_OPERATOR = 'IN';

const AssetFilterView: FilterView = memo(({attribute, filter, onFilterUpdated, context}: AssetFilterViewProps) => {
  if (!(attribute instanceof ConcreteAssetAttribute || attribute instanceof ConcreteAssetCollectionAttribute)) {
    return null;
  }

  const [isOpen, setIsOpen] = useState(false);
  const [hydratedAssets, setHydratedAssets] = useState([]);

  const rawValues = undefined !== filter ? filter.value : [];
  const value = rawValues.map((assetCode: string) => AssetCode.create(assetCode));

  const updateHydratedAssets = async () => {
    if (0 < value.length) {
      const assets = await assetFetcher.fetchByCodes(
        attribute.getAssetType().getAssetFamilyIdentifier(),
        value,
        context,
        true
      );

      setHydratedAssets(assets);
    }
  };

  const emptyFilter = () => {
    setIsOpen(false);
    onFilterUpdated({
      field: getAttributeFilterKey(attribute),
      operator: DEFAULT_OPERATOR,
      value: [],
      context: {},
    });
  };

  useEffect(() => {
    updateHydratedAssets();
  });

  const hint =
    0 === value.length
      ? __('pim_asset_manager.asset.grid.filter.option.all')
      : hydratedAssets.map((asset: NormalizedAsset) => getLabel(asset.labels, context.locale, asset.code)).join(', ');

  return (
    <React.Fragment>
      <span className="AknFilterBox-filterLabel" onClick={() => setIsOpen(true)}>
        {attribute.getLabel(context.locale)}
      </span>
      <span
        className="AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited"
        onClick={() => setIsOpen(true)}
      >
        <span className="AknFilterBox-filterCriteriaHint" title={hint}>
          {hint}
        </span>
        <span className="AknFilterBox-filterCaret" />
      </span>
      {isOpen ? (
        <div>
          <div className="AknDropdown-mask" onClick={() => setIsOpen(false)} />
          <div className="AknFilterBox-filterDetails">
            <div className="AknFilterChoice">
              <div className="AknFilterChoice-header">
                <div className="AknFilterChoice-title">{attribute.getLabel(context.locale)}</div>
                <div className="AknIconButton AknIconButton--erase" onClick={emptyFilter} />
              </div>
              <AssetSelector
                value={value}
                assetFamilyIdentifier={attribute.getAssetType().getAssetFamilyIdentifier()}
                multiple={true}
                compact={true}
                locale={denormalizeLocaleReference(context.locale)}
                channel={denormalizeChannelReference(context.channel)}
                onChange={(assetCodes: AssetCode[]) => {
                  onFilterUpdated({
                    field: getAttributeFilterKey(attribute),
                    operator: DEFAULT_OPERATOR,
                    value: assetCodes.map((assetCode: AssetCode) => assetCode.stringValue()),
                    context: {},
                  });
                }}
              />
            </div>
          </div>
        </div>
      ) : null}
    </React.Fragment>
  );
});

export const filter = connect(
  (state: EditState, ownProps: FilterViewProps): AssetFilterViewProps => {
    return {
      ...ownProps,
      context: {
        locale: state.user.catalogLocale,
        channel: state.user.catalogChannel,
      },
    };
  }
)(AssetFilterView);
