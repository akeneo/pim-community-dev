import $ from 'jquery'
import formBuilder from 'pim/form-builder'

formBuilder.build('pim-app')
  .then(function (form) {
    form.setElement($('.app'))
    form.render()
  })
