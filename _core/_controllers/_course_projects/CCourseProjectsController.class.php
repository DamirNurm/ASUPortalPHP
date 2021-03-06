<?php
/**
 * Курсовое проектирование
 */
class CCourseProjectsController extends CBaseController {
    public function __construct() {
        if (!CSession::isAuth()) {
            if (!in_array(CRequest::getString("action"), $this->allowedAnonymous)) {
                $this->redirectNoAccess();
            }
        }

        $this->_smartyEnabled = true;
        $this->setPageTitle("Курсовые проекты студентов");

        parent::__construct();
    }
    public function actionIndex() {
        $set = new CRecordSet();
        $query = new CQuery();
        $query->select("course_projects.*")
            ->from(TABLE_COURSE_PROJECTS." as course_projects")
			->order("course_projects.order_date desc");
        if (CSessionService::hasAnyRole([ACCESS_LEVEL_READ_OWN_ONLY, ACCESS_LEVEL_WRITE_OWN_ONLY])) {
            $query->condition("course_projects.lecturer_id=".CSession::getCurrentPerson()->getId());
        }
        $set->setQuery($query);
        $courseProjects = new CArrayList();
        foreach ($set->getPaginated()->getItems() as $item) {
            $courseProject = new CCourseProject($item);
            $courseProjects->add($courseProject->getId(), $courseProject);
        }  
		$this->addActionsMenuItem(array(
			array(
                "title" => "Добавить курсовое проектирование",
                "link" => "?action=add",
                "icon" => "actions/list-add.png"
            ),
			array(
				"title" => "Групповые операции",
				"link" => "#",
				"icon" => "apps/utilities-terminal.png",
				"child" => array(
					array(
						"title" => "Удалить выделенные",
						"icon" => "actions/edit-delete.png",
						"form" => "#MainView",
						"link" => "index.php",
						"action" => "delete"
					)
				)
			)
		));
		/**
		 * Параметры для групповой печати по шаблону
		 */
		$this->setData("template", "formset_course_projects");
		$this->setData("selectedDoc", true);
		$this->setData("url", null);
		$this->setData("action", null);
		$this->setData("id", null);
		
        $this->setData("courseProjects", $courseProjects);
        $this->setData("paginator", $set->getPaginator());
        $this->renderView("_course_projects/index.tpl");
    }
    public function actionAdd() {
        $courseProject = new CCourseProject();
        $groups = array();
        if (CSettingsManager::getSettingValue("display_archived_data")) {
            $groups = CStaffManager::getAllStudentGroupsList();
        } else {
            foreach (CStaffManager::getStudentGroupsByYear(CUtils::getCurrentYear())->getItems() as $group) {
                if ($group->getStudentsWithChangeGroupsHistory()->getCount() > 0) {
        			$groups[$group->getId()] = $group->getName();
                }
            }
        }
        $courseProject->lecturer_id = CSession::getCurrentPerson()->getId();
        $disciplines = array();
        if (count(CStaffService::getDisciplinesWithCourseProjectFromLoadByYear(CSession::getCurrentPerson(), CUtils::getCurrentYear())) > 0) {
            $disciplines = CStaffService::getDisciplinesWithCourseProjectFromLoadByYear(CSession::getCurrentPerson(), CUtils::getCurrentYear());
        }
        
        $this->setData("issueProtocols", array());
        $this->setData("progressProtocols", array());
        $this->setData("resultsProtocols", array());
        
        $this->setData("groups", $groups);
        $this->setData("disciplines", $disciplines);
        $this->setData("courseProject", $courseProject);
        $this->setData("disciplineId", 0);
        $this->addActionsMenuItem(array(
            array(
                "title" => "Назад",
                "link" => WEB_ROOT."_modules/_course_projects/index.php",
                "icon" => "actions/edit-undo.png"
            )
        ));		
        $this->renderView("_course_projects/add.tpl");
    }
    public function actionEdit() {
        $courseProject = CBaseManager::getCourseProject(CRequest::getInt("id"));
        $date = date("Y-m-d 00:00:00", strtotime($courseProject->issue_date));
        $years = array();
        foreach (CActiveRecordProvider::getWithCondition(TABLE_YEARS, 'date_start <= "'.$date.'" and date_end >= "'.$date.'"')->getItems() as $ar) {
        	$term = new CTerm($ar);
        	$years[] = $term->getId();
        }
        $groups = array();
        $disciplines = array();
        if (!empty($years)) {
        	$year = CTaxonomyManager::getYear($years[0]);
        	foreach (CStaffManager::getStudentGroupsByYear($year)->getItems() as $group) {
        		if ($group->getStudentsWithChangeGroupsHistory()->getCount() > 0) {
        			$groups[$group->getId()] = $group->getName();
        		}
        	}
            if (count(CStaffService::getDisciplinesWithCourseProjectFromLoadByYear($courseProject->lecturer, $year)) > 0) {
                $disciplines = CStaffService::getDisciplinesWithCourseProjectFromLoadByYear($courseProject->lecturer, $year);
            }
        }
        
        $issueProtocols = array();
        
        foreach (CProtocolManager::getIssueProtocols($courseProject)->getItems() as $issueProtocol) {
        	if ($issueProtocol instanceof CActiveModel) {
        		$issueProtocols[$issueProtocol->getId()] = "Протокол №".$issueProtocol->getNumber()." от ".date("d.m.Y", strtotime($issueProtocol->getDate()));
        	}
        }
        $this->setData("issueProtocols", $issueProtocols);
        
        $progressProtocols = array();
        foreach (CProtocolManager::getProgressProtocols($courseProject)->getItems() as $progressProtocol) {
        	if ($issueProtocol instanceof CActiveModel) {
        		$progressProtocols[$progressProtocol->getId()] = "Протокол №".$progressProtocol->getNumber()." от ".date("d.m.Y", strtotime($progressProtocol->getDate()));
        	}
        }
        $this->setData("progressProtocols", $progressProtocols);
        
        $resultsProtocols = array();
        foreach (CProtocolManager::getResultsProtocols($courseProject)->getItems() as $resultsProtocol) {
        	if ($issueProtocol instanceof CActiveModel) {
        		$resultsProtocols[$resultsProtocol->getId()] = "Протокол №".$resultsProtocol->getNumber()." от ".date("d.m.Y", strtotime($resultsProtocol->getDate()));
        	}
        }
        $this->setData("resultsProtocols", $resultsProtocols);
        
        $this->setData("groups", $groups);
        $this->setData("disciplines", $disciplines);
        $this->setData("courseProject", $courseProject);
        $this->setData("disciplineId", $courseProject->discipline_id);
        $this->addActionsMenuItem(array(
            array(
                "title" => "Назад",
                "link" => WEB_ROOT."_modules/_course_projects/index.php",
                "icon" => "actions/edit-undo.png"
            ),
            array(
                "title" => "Печать по шаблону",
                "link" => "#",
                "icon" => "devices/printer.png",
                "template" => "formset_course_projects"
            )
        ));
        
        /**
         * Параметры для групповой печати по шаблону
         */
        $this->setData("template", "formset_course_projects_tasks");
        $this->setData("selectedDoc", false);
        $this->setData("url", WEB_ROOT."_modules/_course_projects/index.php");
        $this->setData("actionGetTasks", "JSONGetTasks");
        $this->setData("id", CRequest::getInt("id"));
        
        $this->renderView("_course_projects/edit.tpl");
    }
    public function actionJSONGetTasks() {
        $courseProject = CBaseManager::getCourseProject(CRequest::getInt("id"));
        $arr = array();
        foreach ($courseProject->tasks->getItems() as $task) {
            $arr[$task->getId()] = $task->theme;
        }
        echo json_encode($arr);
    }
    public function actionDelete() {
        $courseProject = CBaseManager::getCourseProject(CRequest::getInt("id"));
        if (!is_null($courseProject)) {
            $courseProject->remove();
        }
        $items = CRequest::getArray("selectedInView");
        foreach ($items as $id){
            $courseProject = CBaseManager::getCourseProject($id);
            $courseProject->remove();
        }
        $this->redirect("?action=index");
    }
    public function actionSave() {
        $courseProject = new CCourseProject();
        $courseProject->setAttributes(CRequest::getArray($courseProject::getClassName()));
        if ($courseProject->validate()) {
            $courseProject->save();
            if ($this->continueEdit()) {
                $this->redirect("?action=edit&id=".$courseProject->getId());
            } else {
                $this->redirect(WEB_ROOT."_modules/_course_projects/index.php");
            }
            return true;
        }
        $groups = array();
        foreach (CStaffManager::getStudentGroupsByYear(CUtils::getCurrentYear())->getItems() as $group) {
            if ($group->getStudentsWithChangeGroupsHistory()->getCount() > 0) {
                $groups[$group->getId()] = $group->getName();
            }
        }
        $courseProject->lecturer_id = CSession::getCurrentPerson()->getId();
        $disciplines = array();
        if (count(CStaffService::getDisciplinesWithCourseProjectFromLoadByYear(CSession::getCurrentPerson(), CUtils::getCurrentYear())) > 0) {
            $disciplines = CStaffService::getDisciplinesWithCourseProjectFromLoadByYear(CSession::getCurrentPerson(), CUtils::getCurrentYear());
        }
        $this->setData("issueProtocols", array());
        $this->setData("progressProtocols", array());
        $this->setData("resultsProtocols", array());
        $this->setData("groups", $groups);
        $this->setData("disciplines", $disciplines);
        $this->setData("courseProject", $courseProject);
        $this->setData("disciplineId", $courseProject->discipline_id);
        $this->renderView("_course_projects/add.tpl");
    }
}