import React, {FC} from 'react';
import {KeyFigure} from 'akeneo-design-system';
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
    <KeyFigure icon={icon} title={translate(`pim_catalog_volume.axis.${catalogVolume.name}`)}>
      {catalogVolume.type === 'average_max' ? (
        <>
          {typeof catalogVolume.value === 'object' && catalogVolume.value.average !== undefined && (
            <KeyFigure.Figure label={translate('pim_catalog_volume.mean')}>
              {catalogVolume.value.average}
            </KeyFigure.Figure>
          )}
          {typeof catalogVolume.value === 'object' && catalogVolume.value.max !== undefined && (
            <KeyFigure.Figure label={translate('pim_catalog_volume.max')}>{catalogVolume.value.max}</KeyFigure.Figure>
          )}
        </>
      ) : (
        <KeyFigure.Figure>{catalogVolume.value}</KeyFigure.Figure>
      )}
    </KeyFigure>
  );
};

export {CatalogVolumeKeyFigure};
