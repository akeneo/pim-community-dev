import {
  resetGridFilters,
  updateCodeOrLabelFilter,
  updateStatusFilter
} from '../../../../../../Infrastructure/Symfony/Resources/public/react/application/action/family-mapping/search-franklin-attributes';

it('resets grid filters', () => {
  const dispatch = jest.fn();
  const callback = resetGridFilters();

  expect(typeof callback).toBe('function');
  callback(dispatch);

  expect(dispatch).toHaveBeenCalledWith(updateCodeOrLabelFilter());
  expect(dispatch).toHaveBeenCalledWith(updateStatusFilter(null));
});
