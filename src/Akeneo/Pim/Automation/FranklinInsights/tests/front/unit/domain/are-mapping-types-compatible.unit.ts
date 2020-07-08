/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {areMappingTypesCompatible} from '../../../../Infrastructure/Symfony/Resources/public/react/domain/are-mapping-types-compatible';
import {FranklinAttributeType} from '../../../../Infrastructure/Symfony/Resources/public/react/domain/model/franklin-attribute-type.enum';
import {AttributeType} from '../../../../Infrastructure/Symfony/Resources/public/react/domain/model/attribute-type.enum';

describe('Domain > are mapping types compatible', () => {
  test('it determines that a metric franklin attribute is compatible with a pim attribute', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.METRIC, AttributeType.TEXT)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.METRIC, AttributeType.TEXTAREA)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.METRIC, AttributeType.METRIC)).toBe(true);
  });

  test('it determines that a metric franklin attribute is not compatible with a pim attribute', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.METRIC, AttributeType.SIMPLESELECT)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.METRIC, AttributeType.MULTISELECT)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.METRIC, AttributeType.NUMBER)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.METRIC, AttributeType.BOOLEAN)).toBe(false);
  });

  test('it determines that a select franklin attribute is compatible with a pim attribute', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.SELECT, AttributeType.TEXT)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.SELECT, AttributeType.TEXTAREA)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.SELECT, AttributeType.SIMPLESELECT)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.SELECT, AttributeType.MULTISELECT)).toBe(true);
  });

  test('it determines that a select franklin attribute is not compatible with a pim attribute', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.SELECT, AttributeType.NUMBER)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.SELECT, AttributeType.BOOLEAN)).toBe(false);
  });

  test('it determines that a multi select franklin attribute is compatible with a pim attribute', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.MULTISELECT, AttributeType.TEXT)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.MULTISELECT, AttributeType.TEXTAREA)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.MULTISELECT, AttributeType.SIMPLESELECT)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.MULTISELECT, AttributeType.MULTISELECT)).toBe(true);
  });

  test('it determines that a multi select franklin attribute is not compatible with a pim attribute', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.MULTISELECT, AttributeType.NUMBER)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.MULTISELECT, AttributeType.BOOLEAN)).toBe(false);
  });

  test('it determines that a number franklin attribute is compatible with a pim attribute', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.NUMBER, AttributeType.TEXT)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.NUMBER, AttributeType.TEXTAREA)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.NUMBER, AttributeType.NUMBER)).toBe(true);
  });

  test('it determines that a number franklin attribute is not compatible with a pim attribute', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.NUMBER, AttributeType.SIMPLESELECT)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.NUMBER, AttributeType.MULTISELECT)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.NUMBER, AttributeType.BOOLEAN)).toBe(false);
  });

  test('it determines that a text franklin attribute is compatible with a pim attribute', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.TEXT, AttributeType.TEXT)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.TEXT, AttributeType.TEXTAREA)).toBe(true);
  });

  test('it determines that a text franklin attribute is not compatible with a pim attribute', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.TEXT, AttributeType.NUMBER)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.TEXT, AttributeType.BOOLEAN)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.TEXT, AttributeType.SIMPLESELECT)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.TEXT, AttributeType.MULTISELECT)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.TEXT, AttributeType.METRIC)).toBe(false);
  });

  test('it determines that a boolean franklin attribute is compatible with a pim attribute', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.BOOLEAN, AttributeType.BOOLEAN)).toBe(true);
  });

  test('it determines that a boolean franklin attribute is not compatible with a pim attribute', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.BOOLEAN, AttributeType.NUMBER)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.BOOLEAN, AttributeType.SIMPLESELECT)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.BOOLEAN, AttributeType.MULTISELECT)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.BOOLEAN, AttributeType.METRIC)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.BOOLEAN, AttributeType.TEXT)).toBe(false);
    expect(areMappingTypesCompatible(FranklinAttributeType.BOOLEAN, AttributeType.TEXTAREA)).toBe(false);
  });

  test('it determines that a franklin attribute is compatible if the pim attribute is null', () => {
    expect(areMappingTypesCompatible(FranklinAttributeType.TEXT, null)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.NUMBER, null)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.METRIC, null)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.SELECT, null)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.MULTISELECT, null)).toBe(true);
    expect(areMappingTypesCompatible(FranklinAttributeType.BOOLEAN, null)).toBe(true);
  });
});
