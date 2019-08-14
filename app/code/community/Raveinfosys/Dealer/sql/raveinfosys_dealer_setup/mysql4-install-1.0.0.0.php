<?php
/**
 * Create customer and customer address attributes
 */
$installer = $this;
$installer->startSetup();

$setup = Mage::getModel('customer/entity_setup', 'core_setup');

$setup->addAttribute('customer_address', 'geo_latitude', array(
    'type' => 'varchar',
    'input' => 'text',
    'visible' => true,
    'required' => false,
    'backend_label' => 'Geo Latitude',
    'label' => 'Geo Latitude',
    'frontend' => '',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'user_defined' => false,
    'default' => '',
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => true,
    'unique' => false,
    'note'   => 'If empty, it will generate automatically',
));

$setup->addAttribute('customer_address', 'geo_longitude', array(
    'type' => 'varchar',
    'input' => 'text',
    'visible' => true,
    'required' => false,
    'backend_label' => 'Geo Longitude',
    'label' => 'Geo Longitude',
    'frontend' => '',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'user_defined' => false,
    'default' => '',
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => true,
    'unique' => false,
    'note'   => 'If empty, it will generate automatically',
));

$setup->addAttribute('customer_address', 'use_for_dealer_search', array(
    'type' => 'int',
    'input' => 'select',
    'visible' => true,
    'required' => false,
    'backend_label' => 'Use In Dealer Search',
    'label' => 'Use In Dealer Search',
    'frontend' => '',
    'class' => '',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'user_defined' => false,
    'default' => 0,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => true,
    'unique' => false,
));

/*
 * Adding additional fields to edit customer admin area.
 */
$setup->addAttribute('customer', 'website_url', array(
    'backend_label' => 'Website Url',
    'label' => 'Dealer Website Url',
    'type' => 'varchar',
    'frontend' => '',
    'input' => 'text',
    'class' => '',
    'source' => '',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => '',
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => true,
    'unique' => false,
));

$setup->addAttribute('customer', 'is_active_dealer', array(
    'backend_label' => 'Is Active Dealer',
    'label' => 'Is Active Dealer',
    'type' => 'int',
    'frontend' => '',
    'input' => 'select',
    'class' => '',
    'source' => 'eav/entity_attribute_source_boolean',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
    'visible' => true,
    'required' => false,
    'user_defined' => false,
    'default' => 0,
    'searchable' => false,
    'filterable' => false,
    'comparable' => false,
    'visible_on_front' => true,
    'unique' => false,
));

$_config = Mage::getSingleton("eav/config");

$website = $_config->getAttribute("customer", "website_url");
$isActiveDealer = $_config->getAttribute("customer", "is_active_dealer");

$geoLatitude = $_config->getAttribute("customer_address", "geo_latitude");
$geoLongitude = $_config->getAttribute("customer_address", "geo_longitude");
$useForDealerSearch = $_config->getAttribute("customer_address", "use_for_dealer_search");

$used_in_forms = array();
$used_in_forms[] = "adminhtml_customer";
$used_in_forms[] = "adminhtml_customer_address";

// Customer Address Admin

$geoLatitude->setData("used_in_forms", $used_in_forms)
        ->setData("is_used_for_customer_segment", false)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 200);
$geoLatitude->save();

$geoLongitude->setData("used_in_forms", $used_in_forms)
        ->setData("is_used_for_customer_segment", false)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 201);
$geoLongitude->save();

$useForDealerSearch->setData("used_in_forms", $used_in_forms)
        ->setData("is_used_for_customer_segment", false)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 202);
$useForDealerSearch->save();

// Customer Admin

$website->setData("used_in_forms", $used_in_forms)
        ->setData("is_used_for_customer_segment", false)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 99);
$website->save();

$isActiveDealer->setData("used_in_forms", $used_in_forms)
        ->setData("is_used_for_customer_segment", false)
        ->setData("is_system", 0)
        ->setData("is_user_defined", 1)
        ->setData("is_visible", 1)
        ->setData("sort_order", 100);
$isActiveDealer->save();

/*
 *  Create "Dealer" customer group 
 */
$code = 'Dealer';
$collection = Mage::getModel('customer/group')->getCollection() //get a list of groups
    ->addFieldToFilter('customer_group_code', $code);// filter by group code
$group = Mage::getModel('customer/group')->load($collection->getFirstItem()->getId());
$group->setCode($code); //set the code
$group->setTaxClassId(3); //set tax class
$group->save(); 

$installer->endSetup();
