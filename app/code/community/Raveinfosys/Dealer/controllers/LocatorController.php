<?php

class Raveinfosys_Dealer_LocatorController extends Mage_Core_Controller_Front_Action {    
    /**
     * Search for dealer
     */
    public function searchAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * For map display for individual dealer location
     */
    public function mapAction() {
        $this->loadLayout();
        $this->renderLayout(); 
    }
}
