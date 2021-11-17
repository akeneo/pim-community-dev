import { ReactElement } from "react";
import { IconProps } from "akeneo-design-system";

type VolumeCounter = number;

type VolumeAverageMaxValue = {
  average: number;
  max: number;
};

type CatalogVolume = {
    value: VolumeAverageMaxValue | VolumeCounter;
    has_warning?: boolean; // @deprecated
    type: string;
};

type Axe = {
  name: string;
  volumes: string[];
}

type IconsMapping = {
  [volumeName: string]: any;
};

export type {CatalogVolume, Axe, IconsMapping};
