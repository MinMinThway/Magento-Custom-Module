var config = {
    config: {
        mixins: {
            'Magento_Ui/js/lib/validation/validator': {
                'MMT_Checkout/js/validator-mixin': true
            },
            'Magento_Checkout/js/action/select-shipping-method': {
                'MMT_Checkout/js/action/select-shipping-method-mixin': true
            }
        }
    },
    map: {
        '*': {
            'Magento_Checkout/template/shipping-address/shipping-method-item': 'MMT_Checkout/template/shipping-address/shipping-method-item',
            'Magento_Checkout/template/shipping-address/shipping-method-list': 'MMT_Checkout/template/shipping-address/shipping-method-list'
        }
    }
};