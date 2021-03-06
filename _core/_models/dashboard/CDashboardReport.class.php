<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksandr
 * Date: 19.04.14
 * Time: 16:09
 * To change this template use File | Settings | File Templates.
 */

class CDashboardReport extends CActiveModel{
    protected $_table = TABLE_DASHBOARD_REPORTS;
    
    protected function relations() {
    	return array(
    		"report" => array(
    			"relationPower" => RELATION_HAS_ONE,
    			"storageField" => "report_id",
    			"targetClass" => "CReport"
    		)
    	);
    }
}