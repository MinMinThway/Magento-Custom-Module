var config = {
    map: {
        '*': {
            'Magento_Customer/messages/customerAlreadyExistsErrorMessage.phtml': 'MMT_Customer/messages/customerAlreadyExistsErrorMessage.phtml',
            'Magento_Customer/js/view/authentication-popup': 'MMT_Customer/js/view/authentication-popup'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'MMT_Customer/js/validation-mixin': true,
                'MMT_Customer/js/validation-tandc-mixin': true
            }
        }
    }
}