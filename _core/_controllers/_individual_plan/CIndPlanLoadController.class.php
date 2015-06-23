<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksandr
 * Date: 05.08.13
 * Time: 20:23
 * To change this template use File | Settings | File Templates.
 */

class CIndPlanLoadController extends CBaseController{
    public function __construct() {
        if (!CSession::isAuth()) {
            $this->redirectNoAccess();
        }
        if (CSession::getCurrentUser()->getLevelForCurrentTask() == ACCESS_LEVEL_NO_ACCESS) {
            $this->redirectNoAccess();
        }

        $this->_smartyEnabled = true;
        $this->setPageTitle("Учебная нагрузка");

        parent::__construct();
    }
    public function actionIndex() {
        $set = new CRecordSet(true);
        $query = new CQuery();
        $set->setQuery($query);

        $query->select("p.*")
            ->from(TABLE_PERSON." as p")
            ->order("p.fio asc");
        if(CSession::getCurrentUser()->getLevelForCurrentTask() == ACCESS_LEVEL_WRITE_OWN_ONLY || 
        CSession::getCurrentUser()->getLevelForCurrentTask() == ACCESS_LEVEL_READ_OWN_ONLY){
            if(is_null(CSession::getCurrentPerson())){
                $query->condition("p.id = 0");
            } else {
                $query->condition("p.id = ".CSession::getCurrentPerson()->getId());
            }
        }

        $persons = new CArrayList();

        foreach ($set->getPaginated()->getItems() as $ar) {
            $person = new CPerson($ar);
            $persons->add($person->getId(), $person);
        }

        $this->addActionsMenuItem(
            array(
                array(
                    "title" => "Назад",
                    "link" => "index.php",
                    "icon" => "actions/edit-undo.png"
                ),
                array(
                    "title" => "Печать по шаблону",
                    "link" => "#",
                    "icon" => "devices/printer.png",
                    "template" => "formset_ind_plan_view"
                )
            )
        );

        $this->setData("paginator", $set->getPaginator());
        $this->setData("persons", $persons);
        $this->renderView("_individual_plan/load/index.tpl");
    }
    public function actionView() {
        $person = CStaffManager::getPerson(CRequest::getInt("id"));
        $year = CRequest::getInt("year");
        /**
         * Формируем меню
         */
        $this->addActionsMenuItem(array(
            array(
                "title" => "Назад",
                "link" => "load.php?action=index",
                "icon" => "actions/edit-undo.png"
            ),
            array(
                "title" => "Добавить",
                "link" => "load.php?action=add&id=".$person->getId()."&year=".$year,
                "icon" => "actions/list-add.png"
            ),
            array(
                "title" => "Печать по шаблону",
                "link" => "#",
                "icon" => "devices/printer.png",
                "template" => "formset_individual_plan"
            )
        ));
        $this->setData("person", $person);
        $this->renderView("_individual_plan/load/view.tpl");
    }
    public function actionAdd() {
        $load = new CIndPlanPersonLoad();
        $load->person_id = CRequest::getInt("id");
        $year = CRequest::getInt("year");
        $this->addActionsMenuItem(
        	array(
        		"title" => "Назад",
        		"link" => "load.php?action=view&id=".$load->person_id."&year=".$year,
        		"icon" => "actions/edit-undo.png"
        	)
        );
        $this->setData("load", $load);
        $this->renderView("_individual_plan/load/add.tpl");
    }
    public function actionEdit() {
        $load = CIndPlanManager::getLoad(CRequest::getInt("id"));
        $year = CRequest::getInt("year");
        $this->addActionsMenuItem(
        		array(
        				"title" => "Назад",
        				"link" => "load.php?action=view&id=".$load->person_id."&year=".$year,
        				"icon" => "actions/edit-undo.png"
        		)
        );
        $this->setData("load", $load);
        $this->renderView("_individual_plan/load/edit.tpl");
    }
    public function actionSave() {
        $load = new CIndPlanPersonLoad();
        $load->setAttributes(CRequest::getArray($load::getClassName()));
        if ($load->validate()) {
            $load->save();
            if ($this->continueEdit()) {
                $this->redirect("?action=edit&id=".$load->getId()."&year=".$load->year_id);
            } else {
                $this->redirect("?action=view&id=".$load->person_id."&year=".$load->year_id);
            }
            return true;
        }
        $this->setData("load", $load);
        $this->renderView("_individual_plan/load/add.tpl");
    }
    public function actionDelete() {
        $load = CIndPlanManager::getLoad(CRequest::getInt("id"));
        $person = $load->person;
        $year = $load->year_id;
        $load->remove();
        $this->redirect("?action=view&id=".$person->getId()."&year=".$year);
    }
    public function actionSearch() {
    	$res = array();
    	$term = CRequest::getString("query");
    	/**
    	 * Поиск по ФИО
    	*/
    	$query = new CQuery();
    	$query->select("distinct(person.id) as id, person.fio as name")
    	->from(TABLE_PERSON." as person")
    	->condition("person.fio like '%".$term."%'")
    	->limit(0, 5);
    	foreach ($query->execute()->getItems() as $item) {
    		$res[] = array(
    				"field" => "id",
    				"value" => $item["id"],
    				"label" => $item["name"],
    				"class" => "CPerson"
    		);
    	}
    	echo json_encode($res);
    }
}
