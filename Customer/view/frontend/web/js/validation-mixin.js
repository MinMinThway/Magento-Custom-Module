define(['jquery'], function ($) {
  'use strict';

  return function () {
    $.validator.addMethod(
      'validate-phone-number-custom',
      function (value) {
        if (value === '' || value == null || value.length === 0) {
          return false;
        } else if (/^[0-9+]*$/.test(value)) {
          return /^(09|959|\+)([0-9]{6,15})$/i.test(value);
        } else {
          return /^([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*@([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z0-9-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*\.(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]){2,})$/i.test(value);//eslint-disable-line max-len
        }
      },
      $.mage.__('Please enter valid phone number(09xxxxxxx) or (959xxxxxxx) or (+959xxxxxxxx) or valid email address (Ex: johndoe@domain.com).')
    )
  }
});
