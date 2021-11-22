import React, {FC} from 'react';
import {KeyFigure as DsmKeyFigure} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {CatalogVolume} from './model/catalog-volume';
import {useCatalogVolumeIcon} from './hooks/useCatalogVolumeIcon';

type Props = {
  catalogVolume: CatalogVolume;
};

const CatalogVolumeKeyFigure: FC<Props> = ({catalogVolume}) => {
  const translate = useTranslate();
  const icon = useCatalogVolumeIcon(catalogVolume.name);

  return (
    <DsmKeyFigure icon={icon} title={translate(`pim_catalog_volume.axis.${catalogVolume.name}`)}>
      {catalogVolume.type === 'average_max' ? (
        <>
          {typeof catalogVolume.value === 'object' && catalogVolume.value.average !== undefined && (
            <DsmKeyFigure.Figure label={translate('pim_catalog_volume.mean')}>
              {catalogVolume.value.average}
            </DsmKeyFigure.Figure>
          )}
          {typeof catalogVolume.value === 'object' && catalogVolume.value.max !== undefined && (
            <DsmKeyFigure.Figure label={translate('pim_catalog_volume.max')}>
              {catalogVolume.value.max}
            </DsmKeyFigure.Figure>
          )}
        </>
      ) : (
        <DsmKeyFigure.Figure>{catalogVolume.value}</DsmKeyFigure.Figure>
      )}
    </DsmKeyFigure>
  );
};

export {CatalogVolumeKeyFigure};
