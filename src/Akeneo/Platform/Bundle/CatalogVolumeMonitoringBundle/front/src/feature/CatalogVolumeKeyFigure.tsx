import React, {FC} from 'react';
import {KeyFigure} from 'akeneo-design-system';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {CatalogVolume} from './model/catalog-volume';
import {useCatalogVolumeIcon} from './hooks/useCatalogVolumeIcon';

type Props = {
  catalogVolume: CatalogVolume;
};

const CatalogVolumeKeyFigure: FC<Props> = ({catalogVolume}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const icon = useCatalogVolumeIcon(catalogVolume.name);
  const userLocale = userContext.get('uiLocale').split('_')[0];

  return (
    <>
      {catalogVolume.value !== null && (
        <>
          {catalogVolume.type === 'average_max' && typeof catalogVolume.value === 'object' && (
            <KeyFigure icon={icon} title={translate(`pim_catalog_volume.axis.${catalogVolume.name}`)}>
              {catalogVolume.value.average !== undefined && (
                <KeyFigure.Figure label={translate('pim_catalog_volume.mean')}>
                  {catalogVolume.value.average.toLocaleString(userLocale, {useGrouping: true})}
                </KeyFigure.Figure>
              )}
              {catalogVolume.value.max !== undefined && (
                <KeyFigure.Figure label={translate('pim_catalog_volume.max')}>
                  {catalogVolume.value.max.toLocaleString(userLocale, {useGrouping: true})}
                </KeyFigure.Figure>
              )}
            </KeyFigure>
          )}

          {catalogVolume.type === 'count' && typeof catalogVolume.value !== 'object' && (
            <KeyFigure icon={icon} title={translate(`pim_catalog_volume.axis.${catalogVolume.name}`)}>
              <KeyFigure.Figure>{catalogVolume.value.toLocaleString(userLocale, {useGrouping: true})}</KeyFigure.Figure>
            </KeyFigure>
          )}
        </>
      )}
    </>
  );
};

export {CatalogVolumeKeyFigure};
