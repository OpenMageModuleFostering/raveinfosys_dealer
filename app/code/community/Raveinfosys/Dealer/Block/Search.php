<?php
class Raveinfosys_Dealer_Block_Search extends Mage_Core_Block_Template
{
    /**
     * Set search template for dealer search
     */
    
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('raveinfosys/dealer/search.phtml');
        $this->setCollection($this->getCollectionAll());
    }
    
    /**
     * For pagination
     */
    
    protected function _prepareLayout() {
        parent::_prepareLayout(); 
        $pager = $this->getLayout()->createBlock('page/html_pager', 'custom.pager'); 
        $pager->setAvailableLimit(array(6=>6,12=>12,18=>18,'all'=>'all')); 
        $pager->setCollection($this->getCollection()); 
        $this->setChild('pager', $pager); 
        $this->getCollection()->load(); 
        return $this; 
    }
    
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * To get form action for delear search
     */
    public function getFormAction() 
    {
        return Mage::getUrl('dealer/locator/search');
    }
    
    /**
     * To get dealers collection and apply filters
     */
    public function getCollectionAll(){
         
        $helper = Mage::helper('raveinfosys_dealer');
        $searchTypeZip = $helper->getSearchTypeZip();
        $searchTypeState = $helper->getSearchTypeState();
        $searchTypeCompany = $helper->getSearchTypeCompany();
        $searchTypeDefault = $helper->getSearchTypeBoxDefault();
        
        $searchRequest = $this->getRequest();
        $searchType = $searchRequest->getParam('search_type', false);
        $state = $searchRequest->getParam($searchTypeState, false);
        $company = $searchRequest->getParam($searchTypeCompany, false);
        $zip = $searchRequest->getParam($searchTypeZip, false);
        $radius = $searchRequest->getParam('radius', false);

        if (isset($radius) && $radius == '0') {
            $radius = 0;
        } else {
            $radius = (int) $radius;
        }

        Mage::register('dealer_search_request', $searchRequest);

        if (($searchType == $searchTypeState) && !empty($state)) {
            $customerGroup = Mage::getModel('customer/group');
            $customerGroup->load($helper->getDealersCustomerGroup(), 'customer_group_code');

            $collection = Mage::getResourceModel('customer/address_collection')
                  ->addAttributeToSelect('*')
                  ->addAttributeToFilter('use_for_dealer_search',array('eq'=>1))
                  
                  ->joinAttribute('group_id', 'customer/group_id', 'parent_id', null, 'left')
                  ->addAttributeToFilter('group_id', array('eq' => $customerGroup->getId()))
                  ->joinAttribute('is_active_dealer', 'customer/is_active_dealer', 'parent_id', null, 'left')
                  ->addAttributeToFilter('is_active_dealer',array('eq'=>1))
                
                  ->joinAttribute('email', 'customer/email', 'parent_id', null, 'left')
                  ->joinAttribute('website_url', 'customer/website_url', 'parent_id', null, 'left')
                
                ->addAttributeToFilter('geo_latitude', array('notnull' => true))
                ->addAttributeToFilter('geo_longitude', array('notnull' => true))

                ->addAttributeToFilter('region', array('like' => '%' . $state . '%'))
                ->addAttributeToFilter('country_id',array('eq'=>$helper->getCountryCodeToSearch()))
                ->addAttributeToSort('company', 'ASC')
                ->addAttributeToSort('city', 'ASC');
            
        } else if (($searchType == $searchTypeCompany) && !empty($company)) {
            $customerGroup = Mage::getModel('customer/group');
            $customerGroup->load($helper->getDealersCustomerGroup(), 'customer_group_code');

            $collection = Mage::getResourceModel('customer/address_collection')
                  ->addAttributeToSelect('*')
                  ->addAttributeToFilter('use_for_dealer_search',array('eq'=>1))
                  
                  ->joinAttribute('group_id', 'customer/group_id', 'parent_id', null, 'left')
                  ->addAttributeToFilter('group_id', array('eq' => $customerGroup->getId()))
                  ->joinAttribute('is_active_dealer', 'customer/is_active_dealer', 'parent_id', null, 'left')
                  ->addAttributeToFilter('is_active_dealer',array('eq'=>1))
                
                  ->joinAttribute('email', 'customer/email', 'parent_id', null, 'left')
                  ->joinAttribute('website_url', 'customer/website_url', 'parent_id', null, 'left')
                    
                ->addAttributeToFilter('country_id',array('eq'=>$helper->getCountryCodeToSearch()))
                ->addAttributeToFilter('geo_latitude', array('notnull' => true))
                ->addAttributeToFilter('geo_longitude', array('notnull' => true))
                ->addAttributeToFilter('company', array('like' => '%' . $company . '%'))
                ->addAttributeToSort('city', 'ASC');
           
        } else if (($searchType == $searchTypeZip) && ($radius >= 0)) {

            /* Fake an address based on the provided ZIP */
            $currentAddress = new Mage_Customer_Model_Address();
            $currentAddress->setPostcode($zip);
            $currentAddress->setCountryId($helper->getCountryCodeToSearch());
            /* get GEO for entire faked address/ZIP */
            $centerCoordinates = $helper->getAddressGeoCoordinates($currentAddress, false);
            if(count($centerCoordinates)){
                $centerLat = $centerCoordinates[0];
                $centerLng = $centerCoordinates[1];

                $collection = $helper->getNearbyDealersLocations($radius, $centerLat, $centerLng);
                $collection->addAttributeToFilter('country_id',array('eq'=>$helper->getCountryCodeToSearch()));
                
                Mage::register('dealer_search_center_lat', $centerLat);
                Mage::register('dealer_search_center_lon', $centerLng);
            
            }else{//empty collection
                Mage::register('search_type_default', $searchTypeDefault);
                $collection = Mage::getResourceModel('customer/address_collection')
                  ->joinAttribute('group_id', 'customer/group_id', 'parent_id', null, 'left')
                  ->addAttributeToFilter('group_id', array('eq' => -1));
            }
        }else{
            Mage::register('search_type_default', $searchTypeDefault);
            $collection = Mage::getResourceModel('customer/address_collection')
                  ->joinAttribute('group_id', 'customer/group_id', 'parent_id', null, 'left')
                  ->addAttributeToFilter('group_id', array('eq' => -1));
        }
        
        return $collection;
     }
}
