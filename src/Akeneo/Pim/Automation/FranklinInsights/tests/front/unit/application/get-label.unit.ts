import {getLabel} from '../../../../Infrastructure/Symfony/Resources/public/react/application/get-label';

describe('Application > GetLabel', () => {
  it('it returns the label for the expected locale', () => {
    const labels = {
      en_US: 'My English Label'
    };
    const locale = 'en_US';
    const fallbackLabel = 'my fallback label';

    const label = getLabel(labels, locale, fallbackLabel);

    expect(label).toEqual('My English Label');
  });

  it('it returns the fallback label when the locale is not defined', () => {
    const labels = {
      en_US: 'My English Label'
    };
    const locale = 'fr_FR';
    const fallbackLabel = 'my fallback label';

    const label = getLabel(labels, locale, fallbackLabel);

    expect(label).toEqual('[my fallback label]');
  });

  it('it returns the fallback label when there any locale defined', () => {
    const labels = null;
    const locale = 'en_US';
    const fallbackLabel = 'my fallback label';

    const label = getLabel(labels, locale, fallbackLabel);

    expect(label).toEqual('[my fallback label]');
  });
});
