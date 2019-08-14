<?php

class Raveinfosys_Dealer_Model_Config_Searchtypes_Options 
{
    /* Used for options list on configuration variable */
    public function toOptionArray() {

        $zipKey = Raveinfosys_Dealer_Helper_Data::SEARCH_TYPE_ZIP;
        $customerGroup[0] = array('value' => $zipKey, 'label' => ucfirst($zipKey));
        
        $stateKey = Raveinfosys_Dealer_Helper_Data::SEARCH_TYPE_STATE;
        $customerGroup[1] = array('value' => $stateKey, 'label' => ucfirst($stateKey));
        
        $companyKey = Raveinfosys_Dealer_Helper_Data::SEARCH_TYPE_COMPANY;
        $customerGroup[2] = array('value' => $companyKey, 'label' => ucfirst($companyKey));
        
        return $customerGroup;
    }
}

