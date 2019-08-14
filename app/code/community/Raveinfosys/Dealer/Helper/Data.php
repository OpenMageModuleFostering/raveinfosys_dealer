<?php
class Raveinfosys_Dealer_Helper_Data extends Mage_Core_Helper_Data {

    const GOOGLE_MAPS_HOST = 'maps.googleapis.com';
    const CONFIG_PATH_DEALERS_CUSTOMER_GROUP = 'raveinfosys_dealer_config/dealers_customer_group/default_dealers_customer_group';
    const CONFIG_PATH_SEARCH_RADIUS_OPTIONS = 'raveinfosys_dealer_config/search/radius_options';
    const CONFIG_PATH_SEARCH_RADIUS_OPTION_DEFAULT = 'raveinfosys_dealer_config/search/default_radius_option';
    const CONFIG_PATH_SEARCH_BOX_SEARCH_TYPE_DEFAULT = 'raveinfosys_dealer_config/search/default_box_search_type';
    const CONFIG_PATH_SEARCH_BY_ZIP = 'raveinfosys_dealer_config/search/by_zip';
    const CONFIG_PATH_SEARCH_BY_STATE = 'raveinfosys_dealer_config/search/by_state';
    const CONFIG_PATH_SEARCH_BY_COMPANY = 'raveinfosys_dealer_config/search/by_company';
    const SEARCH_TYPE_ZIP = 'zip';
    const SEARCH_TYPE_STATE = 'state';
    const SEARCH_TYPE_COMPANY = 'company';
    const SEARCH_CUSTOMER_GROUP = 'Dealer';
    const CONFIG_PATH_DEAFULT_SEARCH_COUNTRY = 'raveinfosys_dealer_config/search/default_country';

    public function getCountryCodeToSearch(){
        return Mage::getStoreConfig(self::CONFIG_PATH_DEAFULT_SEARCH_COUNTRY);
    }
    
    public function getSearchTypeBoxDefault() {
        return Mage::getStoreConfig(self::CONFIG_PATH_SEARCH_BOX_SEARCH_TYPE_DEFAULT);
    }

    public function getSearchTypeZip() {
        return self::SEARCH_TYPE_ZIP;
    }

    public function getSearchTypeState() {
        return self::SEARCH_TYPE_STATE;
    }

    public function getSearchTypeCompany() {
        return self::SEARCH_TYPE_COMPANY;
    }

    public function getIsSearchByZipEnabled() {
        return Mage::getStoreConfig(self::CONFIG_PATH_SEARCH_BY_ZIP);
    }

    public function getIsSearchByStateEnabled() {
        return Mage::getStoreConfig(self::CONFIG_PATH_SEARCH_BY_STATE);
    }

    public function getIsSearchByCompanyEnabled() {
        return Mage::getStoreConfig(self::CONFIG_PATH_SEARCH_BY_COMPANY);
    }

    public function getDealersCustomerGroup() {
        return self::SEARCH_CUSTOMER_GROUP;
    }

    public function getSearchRadiusOptions() {
        $options = explode(',', Mage::getStoreConfig(self::CONFIG_PATH_SEARCH_RADIUS_OPTIONS));
        return $options;
    }

    public function getSearchRadiusOptionDefault() {
        return Mage::getStoreConfig(self::CONFIG_PATH_SEARCH_RADIUS_OPTION_DEFAULT);
    }

    // To get GeoCordinates of any address
    public function getAddressGeoCoordinates(Mage_Customer_Model_Address $address, $saveCoordinatesToAddress = true) {

        $coordinates = array();
        
        if($saveCoordinatesToAddress){ //request from backend
            $lineAddress = $address->getStreet1() . ', ' . $address->getPostcode() . ' ' . $address->getCity() . ', ' . $address->getCountry();
        }else{ //request from search on frontend
            $componentsStr = 'postal_code:'.$address->getPostcode().'|country:'.$address->getCountry();
        }
        
        $client = new Zend_Http_Client();
        $client->setUri('http://' . self::GOOGLE_MAPS_HOST . '/maps/api/geocode/json');
        $client->setMethod(Zend_Http_Client::GET);
        $client->setConfig(array('maxredirects' => 0, 'timeout' => 60));
        if($saveCoordinatesToAddress){
            $client->setParameterGet('address', $lineAddress);
        }else{
            $client->setParameterGet('components', $componentsStr);
        }
        $client->setParameterGet('sensor', 'false');

        $response = $client->request();

        if ($response->isSuccessful() && $response->getStatus() == 200) {
            $_response = json_decode($response->getBody());
            $_status = @$_response->status;

            if ($_status == 'OK') {
                $_location = @$_response->results[0]->geometry->location;
                $_lat = $_location->lat;
                $_lng = $_location->lng;

                if ($_lat && $_lng) {

                    $coordinates = array($_lat, $_lng);

                    if ($saveCoordinatesToAddress) {
                        try {
                            if(!$address->getGeoLatitude())
                                $address->setGeoLatitude($_lat);
                            if(!$address->getGeoLongitude())
                                $address->setGeoLongitude($_lng);
                        } catch (Exception $e) {
                            Mage::logException($e);
                        }
                    }
                }
            }
        }
        return $coordinates;
    }
    

    /**
     * @return collection of regions for which there are dealers available
     */
    public function getDealerRegions() {
        
        $customerGroup = Mage::getModel('customer/group');
        $customerGroup->load($this->getDealersCustomerGroup(), 'customer_group_code');
        
        $collection = Mage::getResourceModel('customer/address_collection')
                ->joinAttribute('region', 'customer_address/region', 'entity_id', null, 'left')// Just to use for distinct filter
                ->addAttributeToFilter('use_for_dealer_search',array('eq'=>1))
                  
                ->joinAttribute('group_id', 'customer/group_id', 'parent_id', null, 'left')
                ->addAttributeToFilter('group_id', array('eq' => $customerGroup->getId()))
                ->joinAttribute('is_active_dealer', 'customer/is_active_dealer', 'parent_id', null, 'left')
                ->addAttributeToFilter('is_active_dealer',array('eq'=>1))
                ->addAttributeToFilter('country_id',array('eq'=>$this->getCountryCodeToSearch()))
                ;
        $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns($this->getAttributeTableAlias().'region.value');
        $collection->getSelect()->distinct();        
                
        $regions = array();
        foreach ($collection as $region) {
            $_region =  trim($region->getValue());
            if (!empty($_region)) {
                $regions[] = $_region;
            }
        }

        sort($regions);
        return $regions;
    }

    /**
     * @return collection of companies for which there are dealrds available
     */
    public function getDealerCompanies() {

        $customerGroup = Mage::getModel('customer/group');
        $customerGroup->load($this->getDealersCustomerGroup(), 'customer_group_code');
        
        $collection = Mage::getResourceModel('customer/address_collection')
                  ->joinAttribute('company', 'customer_address/company', 'entity_id', null, 'left')// Just to use for distinct filter
                  ->addAttributeToFilter('use_for_dealer_search',array('eq'=>1))
                  
                  ->joinAttribute('group_id', 'customer/group_id', 'parent_id', null, 'left')
                  ->addAttributeToFilter('group_id', array('eq' => $customerGroup->getId()))
                  ->joinAttribute('is_active_dealer', 'customer/is_active_dealer', 'parent_id', null, 'left')
                  ->addAttributeToFilter('is_active_dealer',array('eq'=>1))
                  ->addAttributeToFilter('country_id',array('eq'=>$this->getCountryCodeToSearch()))
                  ;
        
        $collection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns($this->getAttributeTableAlias().'company.value');
        $collection->getSelect()->distinct();        
        $companies = array(); 

        foreach ($collection as $company) {
            $_company = trim($company->getValue());
            if (!empty($_company)) {
                $companies[] = $_company;
            }
        }

        sort($companies);
        return $companies;
    }

    /**
     * Search near by dealer location
     * @return collection for locations for zip/postcode search
     */
    public function getNearbyDealersLocations($radius, $centerLat, $centerLng) {
        
        $customerGroup = Mage::getModel('customer/group');
        $customerGroup->load($this->getDealersCustomerGroup(), 'customer_group_code');

        if (!$customerGroup->getId()) {
            throw new Exception($this->__('Unable to load the customer group.'));
        }

        $attTableAlias = $this->getAttributeTableAlias();
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
                ->addExpressionAttributeToSelect('distance', sprintf("(3959 * acos(cos(radians('%s')) * cos(radians(%sgeo_latitude.value)) * cos(radians(%sgeo_longitude.value) - radians('%s')) + sin(radians('%s')) * sin( radians(%sgeo_latitude.value))))", $centerLat, $attTableAlias, $attTableAlias, $centerLng, $centerLat, $attTableAlias, $radius), array('entity_id'))
                ->addAttributeToSort('distance', 'ASC');
                 
        if ($radius !== 0) {
            $collection->addFieldToFilter('distance', array('lteq' => $radius));
        }
        
        $collection->getSelect()->order('distance ' . Varien_Db_Select::SQL_ASC);
        return $collection;
    }

    /**
     * get attribute table alias as per the magento version
     */
    public function getAttributeTableAlias(){
	$magentoVersion = Mage::getVersion();
        if (version_compare($magentoVersion, '1.6', '>=')){
                return 'at_';
        } else {
                return '_table_';
        }
    }
}