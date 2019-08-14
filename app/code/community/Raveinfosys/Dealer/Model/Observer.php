<?php
class Raveinfosys_Dealer_Model_Observer {
    
    /**
     * Save latitude and longitude for address
     */
    public function setLatLongForAddress($observer) {
        Mage::helper('raveinfosys_dealer')
                ->getAddressGeoCoordinates($observer->getEvent()->getCustomerAddress());
    }
}
