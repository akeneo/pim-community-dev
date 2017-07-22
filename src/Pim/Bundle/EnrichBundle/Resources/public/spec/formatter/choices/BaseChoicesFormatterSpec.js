/* global describe, it, expect, beforeEach, spyOn */

import Formatter from 'pim/formatter/choices/base'
import UserContext from 'pim/user-context'
import i18n from 'pim/i18n'
describe('Base choices formatter', function () {
  beforeEach(function () {
    this.entities = [
      {
        code: 'webcams',
        labels: {
          en_US: 'Webcams',
          fr_FR: 'Webcams',
          de_DE: 'Webcams'
        }
      },
      {
        code: 'mugs',
        labels: {
          en_US: 'Mugs',
          fr_FR: 'Chopes\/Mugs',
          de_DE: 'Tassen'
        }
      }
    ]
  })

  it('provides a method to format a list of choices', function () {
    expect(Formatter.format).toBeDefined()
  })

  it('it formats a list of choices', function () {
    spyOn(UserContext, 'get').and.returnValue('de_DE')
    spyOn(i18n, 'getLabel').and.callThrough()

    expect(Formatter.format(this.entities)).toEqual([
      {
        id: 'webcams',
        text: 'Webcams'
      },
      {
        id: 'mugs',
        text: 'Tassen'
      }
    ])
  })

  it('it formats a list of choices with fallbacks for labels', function () {
    spyOn(UserContext, 'get').and.returnValue('unsupported_locale')
    spyOn(i18n, 'getLabel').and.callThrough()

    expect(Formatter.format(this.entities)).toEqual([
      {
        id: 'webcams',
        text: '[webcams]'
      },
      {
        id: 'mugs',
        text: '[mugs]'
      }
    ])
  })
})
