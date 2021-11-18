import React, {FC} from 'react';
import {KeyFigure as DsmKeyFigure} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {KeyFigure} from './model/catalog-volume';
import {useCatalogVolumeIcon} from './hooks/useCatalogVolumeIcon';

type Props = {
  keyFigure: KeyFigure;
};

const CatalogVolumeKeyFigure: FC<Props> = ({keyFigure}) => {
  const translate = useTranslate();
  const icon = useCatalogVolumeIcon(keyFigure.name);

  return (
    <DsmKeyFigure icon={icon} title={translate(`pim_catalog_volume.axis.${keyFigure.name}`)}>
      {keyFigure.type === 'average_max' ? (
        <>
          {typeof keyFigure.value === 'object'&& keyFigure.value.average !== undefined && <DsmKeyFigure.Figure label={translate('pim_catalog_volume.mean')}>{keyFigure.value.average}</DsmKeyFigure.Figure>}
          {typeof keyFigure.value === 'object'&& keyFigure.value.max !== undefined && <DsmKeyFigure.Figure label={translate('pim_catalog_volume.max')}>{keyFigure.value.max}</DsmKeyFigure.Figure>}
        </>
      ) : <DsmKeyFigure.Figure>{keyFigure.value}</DsmKeyFigure.Figure>}
    </DsmKeyFigure>
  );
}

export {CatalogVolumeKeyFigure};
