<?php

namespace Karlis\Vat\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_KARLIS_VAT_RESOURCE_ID = 'karlis_vat/general/resource_id';
    const CHECKOUT_SESSION_EXCLUDE_TAX_KEY = 'vat_valid_exclude_tax';

    /**
     * Get resource ID from configurations.
     *
     * @return string
     */
    public function getResourceId()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_KARLIS_VAT_RESOURCE_ID, ScopeInterface::SCOPE_STORE);
    }
}
