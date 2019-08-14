<?php

class Raveinfosys_Dealer_Model_Config_Radius_Options 
{
    /* Used for options list on distance radius configuration options */
    public function toOptionArray() {
        return array (
            array ( 'label' => 10, 'value' => 10 ),
            array ( 'label' => 20, 'value' => 20 ),
            array ( 'label' => 50, 'value' => 50 ),
            array ( 'label' => 100,'value' => 100 ),
            array ( 'label' => 200,'value' => 200 ),
            array ( 'label' => 300,'value' => 300 ),
            array ( 'label' => 400,'value' => 400 ),
            array ( 'label' => 500,'value' => 500 ),
            array ( 'label' => 1000,'value' => 1000 ),
            array ( 'label' => 2000,'value' => 2000 )
       ); 
    }
}

