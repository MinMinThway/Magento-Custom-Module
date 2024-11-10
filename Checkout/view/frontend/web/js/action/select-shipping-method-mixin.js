define([
    'mage/utils/wrapper',
    'jquery'
], function (wrapper, $) {
    'use strict';

    return function (selectShippingMethod) {
        return wrapper.wrap(selectShippingMethod, function (_super, shippingMethod) {
            _super(shippingMethod);

            let selector = '#checkout-shipping-method-load .table-checkout-shipping-method .shipping-card';
            // remove class from shipping methods
            $(selector).removeClass('active');
            if (shippingMethod) {
                // lookup shipping method by code and set class
                $(selector).each(function (i, e) {
                    console.log($(e).find('input:radio').val());
                    if (shippingMethod.carrier_code + '_' + shippingMethod.method_code == $(e).find('input:radio').val()) {
                        $(e).addClass('active');
                    }
                });

            }

        });
    };
});