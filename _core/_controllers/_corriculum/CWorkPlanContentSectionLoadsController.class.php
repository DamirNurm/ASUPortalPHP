<?php
class CWorkPlanContentSectionLoadsController extends CBaseController{
    public function __construct() {
        if (!CSession::isAuth()) {
            $action = CRequest::getString("action");
            if ($action == "") {
                $action = "index";
            }
            if (!in_array($action, $this->allowedAnonymous)) {
                $this->redirectNoAccess();
            }
        }

        $this->_smartyEnabled = true;
        $this->setPageTitle("Нагрузка");

        parent::__construct();
    }
    public function actionIndex() {
        $set = new CRecordSet();
        $query = new CQuery();
        $set->setQuery($query);
        $query->select("t.*")
            ->from(TABLE_WORK_PLAN_CONTENT_LOADS." as t")
            ->condition("section_id=".CRequest::getInt("section_id"))
            ->order("t.id asc");
        $objects = new CArrayList();
        foreach ($set->getPaginated()->getItems() as $ar) {
            $object = new CWorkPlanContentSectionLoad($ar);
            $objects->add($object->getId(), $object);
        }
        $this->setData("objects", $objects);
        $this->setData("paginator", $set->getPaginator());
        /**
         * Генерация меню
         */
        $this->addActionsMenuItem(array(
			array(
				"title" => "Назад",
				"link" => "workplancontentsections.php?action=edit&id=".$object->section_id,
				"icon" => "actions/edit-undo.png"
			),
			array(
				"title" => "Обновить",
				"link" => "workplancontentloads.php?action=index&section_id=".CRequest::getInt("section_id"),
				"icon" => "actions/view-refresh.png"
			),
			array(
				"title" => "Добавить нагрузку",
				"link" => "workplancontentloads.php?action=add&id=".CRequest::getInt("section_id"),
				"icon" => "actions/list-add.png"
			),
        ));
        /**
         * Отображение представления
         */
        $this->renderView("_corriculum/_workplan/contentLoads/index.tpl");
    }
    public function actionAdd() {
        $object = new CWorkPlanContentSectionLoad();
        $object->section_id = CRequest::getInt("id");
        $this->setData("object", $object);
        /**
         * Генерация меню
         */
        $this->addActionsMenuItem(array(
            "title" => "Назад",
            "link" => "workplancontentloads.php?action=index&section_id=".$object->section_id,
            "icon" => "actions/edit-undo.png"
        ));
        /**
         * Отображение представления
         */
        $this->renderView("_corriculum/_workplan/contentLoads/add.tpl");
    }
    public function actionEdit() {
        $object = CBaseManager::getWorkPlanContentSectionLoad(CRequest::getInt("id"));
        $this->setData("object", $object);
        /**
         * Генерация меню
         */
        $this->addActionsMenuItem(array(
            "title" => "Назад",
            "link" => "workplancontentsections.php?action=edit&id=".$object->section_id,
            "icon" => "actions/edit-undo.png"
        ));
        $this->addActionsMenuItem(array(
            "title" => "Добавить тему",
            "link" => "workplancontenttopics.php?action=add&id=".$object->getId(),
            "icon" => "actions/list-add.png"
        ));
        $this->addActionsMenuItem(array(
            "title" => "Добавить технологию",
            "link" => "workplancontenttechnologies.php?action=add&id=".$object->getId(),
            "icon" => "actions/list-add.png"
        ));
        $this->addActionsMenuItem(array(
            "title" => "Добавить самостоятельную работу",
            "link" => "workplanselfeducationblocks.php?action=add&id=".$object->getId(),
            "icon" => "actions/list-add.png"
        ));
        /**
         * Отображение представления
         */
        $this->renderView("_corriculum/_workplan/contentLoads/edit.tpl");
    }
    public function actionDelete() {
        $object = CBaseManager::getWorkPlanContentSectionLoad(CRequest::getInt("id"));
        $section = $object->section_id;
        $object->remove();
        $this->redirect("workplancontentloads.php?action=index&section_id=".$section);
    }
    public function actionSave() {
        $object = new CWorkPlanContentSectionLoad();
        $object->setAttributes(CRequest::getArray($object::getClassName()));
        if ($object->validate()) {
            $object->save();
            if ($this->continueEdit()) {
                $this->redirect("workplancontentloads.php?action=index&section_id=".$object->section_id);
            } else {
                $this->redirect("workplancontentloads.php?action=index&section_id=".$object->section_id);
            }
            return true;
        }
        $this->setData("object", $object);
        $this->renderView("_corriculum/_workplan/contentLoads/edit.tpl");
    }
}