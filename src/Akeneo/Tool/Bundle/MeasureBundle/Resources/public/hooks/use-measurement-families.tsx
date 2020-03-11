import {useState, useEffect} from 'react';
import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';

const fetchMeasurementFamilies = (): Promise<MeasurementFamily[]> =>
  new Promise<MeasurementFamily[]>((resolve: any) =>
    setTimeout(
      () =>
        resolve([
          {
            code: 'AREA',
            labels: {en_US: 'Area', fr_FR: 'Aire'},
            standard_unit_code: 'SQUARE_METER',
            units: [
              {
                code: 'SQUARE_METER',
                labels: {en_US: 'Square meter', fr_FR: 'metre carre'},
                symbol: 'm2',
                convert_from_standard: [{operator: 'mul', value: '1'}],
              },
              {
                code: 'SQUARE_METER',
                labels: {en_US: 'Square meter', fr_FR: 'metre carre'},
                symbol: 'm2',
                convert_from_standard: [{operator: 'mul', value: '1'}],
              },
            ],
          },
          {
            code: 'LENGTH',
            labels: {en_US: 'Length', fr_FR: 'Longueur'},
            standard_unit_code: 'KILOMETER',
            units: [
              {
                code: 'KILOMETER',
                labels: {en_US: 'Kilometre', fr_FR: 'Kilomètre'},
                symbol: 'm2',
                convert_from_standard: [{operator: 'mul', value: '1'}],
              },
            ],
          },
          {
            code: 'OTHER',
            labels: {en_US: 'Other', fr_FR: 'Autre'},
            standard_unit_code: 'ANOTHER_ONE',
            units: [
              {
                code: 'ANOTHER_ONE',
                labels: {en_US: 'Other unit', fr_FR: 'Autre unité'},
                symbol: 'm2',
                convert_from_standard: [{operator: 'mul', value: '1'}],
              },
            ],
          },
        ]),
      400
    )
  );

export const useMeasurementFamilies = () => {
  const [measurementFamilies, setMeasurementFamilies] = useState<MeasurementFamily[] | null>(null);

  useEffect(() => {
    (async () => setMeasurementFamilies(await fetchMeasurementFamilies()))();
  }, []);

  return measurementFamilies;
};
