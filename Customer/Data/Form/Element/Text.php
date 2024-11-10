<?php

namespace MMT\Customer\Data\Form\Element;

use Magento\Framework\Data\Form\Element\Text as ElementText;

class Text extends ElementText
{

    public function getLabelHtml($idSuffix = '', $scopeLabel = '')
    {
        $scopeLabel = $scopeLabel ? ' data-config-scope="' . $scopeLabel . '"' : '';
        $customLabel = $this->getLabel();
        if ($this->getHtmlId() == 'email' && $customLabel == 'Email') {
            $customLabel = 'Email or Phone';
        }

        if ($this->getLabel() !== null) {
            $html = '<label class="label admin__field-label" for="' .
                $this->getHtmlId() . $idSuffix . '"' . $this->_getUiId(
                    'label'
                ) . '><span' . $scopeLabel . '>' . $this->_escape(
                    $customLabel
                ) . '</span></label>' . "\n";
        } else {
            $html = '';
        }
        return $html;
    }
}
