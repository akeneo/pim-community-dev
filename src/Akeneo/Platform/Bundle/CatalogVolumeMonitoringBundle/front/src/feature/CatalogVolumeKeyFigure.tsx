import React, {FC} from 'react';
import {KeyFigure} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {CatalogVolume} from './model/catalog-volume';
import {useCatalogVolumeIcon} from './hooks/useCatalogVolumeIcon';

type Props = {
  name: string;
  volume: CatalogVolume;
};

const CatalogVolumeKeyFigure: FC<Props> = ({name, volume}) => {
  const translate = useTranslate();
  const icon = useCatalogVolumeIcon(name);

  return (
    <KeyFigure icon={icon} title={translate(`pim_catalog_volume.axis.${name}`)}>
      {volume.type === 'average_max' ? (
        <>
          {typeof volume.value === 'object'&& volume.value.average !== undefined && <KeyFigure.Figure label={translate('pim_catalog_volume.mean')}>{volume.value.average}</KeyFigure.Figure>}
          {typeof volume.value === 'object'&& volume.value.max !== undefined && <KeyFigure.Figure label={translate('pim_catalog_volume.max')}>{volume.value.max}</KeyFigure.Figure>}
        </>
      ) : <KeyFigure.Figure>{volume.value}</KeyFigure.Figure>}
    </KeyFigure>
  );
}

export {CatalogVolumeKeyFigure};
