import * as React from 'react';
import {FilterView, FilterViewProps} from 'akeneoassetmanager/application/configuration/value';
import {ConcreteAssetAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset';
import __ from 'akeneoassetmanager/tools/translator';
import {ConcreteAssetCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset-collection';
import AssetSelector from 'akeneoassetmanager/application/component/app/asset-selector';
import AssetCode, {denormalizeAssetCode, assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
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
  const value = rawValues.map((assetCode: string) => denormalizeAssetCode(assetCode));

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

  const [position, setPosition] = React.useState({top: 0, left: 0});
  const labelRef = React.useRef<HTMLSpanElement>(null);
  const openPanel = () => {
    setIsOpen(true);
    if (null !== labelRef.current) {
      const viewportOffset = labelRef.current.getBoundingClientRect();
      setPosition({top: viewportOffset.top, left: viewportOffset.left});
    }
  };

  return (
    <React.Fragment>
      <span ref={labelRef} className="AknFilterBox-filterLabel" onClick={openPanel}>
        {attribute.getLabel(context.locale)}
      </span>
      <span className="AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited" onClick={openPanel}>
        <span className="AknFilterBox-filterCriteriaHint" title={hint}>
          {hint}
        </span>
        <span className="AknFilterBox-filterCaret" />
      </span>
      {isOpen ? (
        <div>
          <div className="AknDropdown-mask" onClick={() => setIsOpen(false)} />
          <div
            className="AknFilterBox-filterDetails AknFilterBox-filterDetails--rightAlign"
            style={{top: `${position.top + 20}px`, left: `${position.left}px`, position: 'fixed'}}
          >
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
                    value: assetCodes.map((assetCode: AssetCode) => assetCodeStringValue(assetCode)),
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

export const filter = AssetFilterView;
