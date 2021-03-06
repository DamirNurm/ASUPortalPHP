<?php

/**
 * Сервис по работе с сотрудниками
 *
 */
class CStaffService {
	
    /**
     * ФИО заведующего кафедрой или исполняющего его обязанности с добавлением названия должности, если $withPost = true
     * 
     * @param boolean $withPost
     * @return string
     */
    public static function getHeadOrActingOfDepartment($withPost) {
        $person = "";
        
        $headOfDepartment = CTaxonomyManager::getLegacyTaxonomy(TABLE_POSTS)->getTerm(CPostConstants::HEAD_OF_DEPARTMENT);
        $actingHeadOfDepartment = CTaxonomyManager::getLegacyTaxonomy(TABLE_POSTS)->getTerm(CPostConstants::ACTING_HEAD_OF_DEPARTMENT);
        
        if (!is_null(CStaffManager::getPersonByPostId($actingHeadOfDepartment->getId()))) {
        	if ($withPost) {
        		$person = $actingHeadOfDepartment->name_short." ".CStaffManager::getPersonByPostId($actingHeadOfDepartment->getId())->getNameShort();
        	} else {
        		$person = CStaffManager::getPersonByPostId($actingHeadOfDepartment->getId())->getNameShort();
        	}
        } else {
        	if ($withPost) {
        		$person = $headOfDepartment->name_short." ".CStaffManager::getPersonByPostId($headOfDepartment->getId())->getNameShort();
        	} else {
        		$person = CStaffManager::getPersonByPostId($headOfDepartment->getId())->getNameShort();
        	}
        }
        
        return $person;
    }
    
    /**
     * ФИО заведующего кафедрой
     *
     * @return string
     */
    public static function getHeadOfDepartment() {
        $headOfDepartment = CTaxonomyManager::getLegacyTaxonomy(TABLE_POSTS)->getTerm(CPostConstants::HEAD_OF_DEPARTMENT);
        $person = CStaffManager::getPersonByPostId($headOfDepartment->getId())->getNameShort();
        return $person;
    }
    
    /**
     * Должность заведующего кафедрой или исполняющего его обязанности
     *
     * @return string
     */
    public static function getPostHeadOfDepartment() {
        $post = "";
    	
        $headOfDepartment = CTaxonomyManager::getLegacyTaxonomy(TABLE_POSTS)->getTerm(CPostConstants::HEAD_OF_DEPARTMENT);
        $actingHeadOfDepartment = CTaxonomyManager::getLegacyTaxonomy(TABLE_POSTS)->getTerm(CPostConstants::ACTING_HEAD_OF_DEPARTMENT);
    	
        if (!is_null(CStaffManager::getPersonByPostId($actingHeadOfDepartment->getId()))) {
        	$post = $actingHeadOfDepartment->name_short;
        } else {
        	$post = $headOfDepartment->name_short;
        }
    	
        return $post;
    }
    
    /**
     * Действующие приказы для указанного года
     *
     * @param CPerson $person
     * @param CTerm $year
     * @return CArrayList
     */
    public static function getActiveOrdersForYear(CPerson $person, CTerm $year) {
        $result = new CArrayList();
        foreach ($person->orders->getItems() as $order) {
            if (CStaffService::orderIsActiveForYear($order, $year)) {
                $result->add($order->getId(), $order);
            }
        }
        return $result;
    }
    
    /**
     * Список действующих приказов для указанного года
     *
     * @param CPerson $person
     * @param CTerm $year
     * @return array
     */
    public static function getActiveOrdersListForYear(CPerson $person, CTerm $year) {
        $result = array();
        foreach (CStaffService::getActiveOrdersForYear($person, $year)->getItems() as $order) {
            $typeMoney = "";
            if ($order->type_money == 2) {
                $typeMoney = "Б";
            } elseif ($order->type_money == 3) {
                $typeMoney = "К";
            }
            $result[$order->getId()] = "Приказ № ".$order->num_order." от ".$order->date_order." (".$order->rate.") ".$typeMoney;
        }
        return $result;
    }
    
    /**
     * Действует ли приказ сотрудника для указанного года
     *
     * @param COrder $order
     * @param CTerm $year
     * @return bool
     */
    public static function orderIsActiveForYear(COrder $order, CTerm $year) {
        $dateStartYear = strtotime($year->date_start);
        $dateBeginOrder = strtotime($order->date_begin);
        $dateEndYear = strtotime($year->date_end);
        $dateEndOrder = strtotime($order->date_end);
        if ($dateBeginOrder < $dateEndYear and $dateStartYear < $dateEndOrder and $dateBeginOrder <= $dateEndYear) {
            return true;
        }
        return false;
    }
    
    /**
     * Сведения об успеваемости студента по дисциплине, преподавателю и виду контроля
     *
     * @param CStudent $student
     * @param CTerm $discipline
     * @param CPerson $lecturer
     * @param CTerm $controlType
     * @param $issueDate
     * 
     * @return CStudentActivity
     */
    public static function getStudentActivityByTypeAndDate(CStudent $student, CTerm $discipline, CPerson $lecturer, CTerm $controlType, $issueDate) {
        $date = date("Y-m-d 00:00:00", strtotime($issueDate));
        $years = array();
        foreach (CActiveRecordProvider::getWithCondition(TABLE_YEARS, 'date_start <= "'.$date.'" and date_end >= "'.$date.'"')->getItems() as $ar) {
            $term = new CTerm($ar);
            $years[] = $term->getId();
        }
        $activity = null;
        if (!empty($years)) {
            $year = CTaxonomyManager::getYear($years[0]);
            foreach (CActiveRecordProvider::getWithCondition(TABLE_STUDENTS_ACTIVITY, '(date_act >= "'.$year->date_start.'" and date_act <= "'.$year->date_end.'") 
                    and student_id = '.$student->getId().' and subject_id = '.$discipline->getId().' and kadri_id = '.$lecturer->getId().' 
                    and study_act_id = '.$controlType->getId())->getItems() as $item) {
                $activity = new CStudentActivity($item);
            }
        }
        return $activity;
    }
    
    /**
     * Проверка успеваемости студента на неудовлетворительные оценки
     *
     * @param CStudentActivity $studentActivity
     * @param $studyMark
     *
     * @return bool
     */
    public static function isStudentActivityWithBadMark(CStudentActivity $studentActivity) {
    	if ($studentActivity->study_mark == CTaxonomyManager::getLegacyTaxonomy("study_marks")->getTerm(CCourseProjectConstants::UNSATISFACTORILY_STUDY_MARK)->getId() or
    			$studentActivity->study_mark == CTaxonomyManager::getLegacyTaxonomy("study_marks")->getTerm(CCourseProjectConstants::FAIL_STUDY_MARK)->getId() or
    			$studentActivity->study_mark == CTaxonomyManager::getLegacyTaxonomy("study_marks")->getTerm(CCourseProjectConstants::ABSENSE_STUDY_MARK)->getId() or
    			$studentActivity->study_mark == CTaxonomyManager::getLegacyTaxonomy("study_marks")->getTerm(CCourseProjectConstants::NOT_DONE_STUDY_MARK)->getId()) {
    		return true;
    	}
        return false;
    }
    
    /**
     * Список дисциплин преподавателя по году, по которым есть нагрузка по курсовому проектированию
     *
     * @param CPerson $lecturer
     * @param CTerm $year
     * 
     * @return array
     */
    public static function getDisciplinesWithCourseProjectFromLoadByYear(CPerson $lecturer, CTerm $year) {
        $disciplines = array();
        foreach (CActiveRecordProvider::getWithCondition(TABLE_WORKLOAD, "person_id = ".$lecturer->getId()." AND year_id = ".$year->getId()." AND _is_last_version = 1")->getItems() as $item) {
        	$studyKursProj = 0;
    		$study = new CStudyLoad($item);
    		foreach ($study->works as $work) {
    			$studyKursProj += $work->getSumWorkHoursByType(CStudyLoadWorkTypeConstants::LABOR_COURSE_PROJECT);
    		}
			if ($studyKursProj != 0) {
				$disciplines[$study->discipline_id] = CDisciplinesManager::getDiscipline($study->discipline_id)->name;
			}
        }
        if (!empty($disciplines)) {
			asort($disciplines);
        }
        return $disciplines;
    }
    
    /**
     * Получить пути к файлам по образованию сотрудника
     * 
     * @param CPerson $person
     * 
     * @return CArrayList - где ключ - ссылка на файл, значение - название файла
     */
    public static function getFilesEducationPerson(CPerson $person) {
    	$files = new CArrayList();
    	$personName = $person->getNameShort();
    	foreach ($person->diploms->getItems() as $diplom) {
    		$link = CFileUtils::getLinkAttachment("file_attach", $diplom);
    		if (!is_null($link)) {
    			$files->add($link, $personName."_Диплом ВУЗа ".$diplom->zaved_name);
    		}
    	}
    	foreach ($person->cources->getItems() as $course) {
    		$link = CFileUtils::getLinkAttachment("file_attach", $course);
    		if (!is_null($link)) {
    			$files->add($link, $personName."_Курс ".$course->name);
    		}
    	}
    	foreach ($person->phdpapers->getItems() as $paper) {
    		$link = CFileUtils::getLinkAttachment("file_attach", $paper);
    		if (!is_null($link)) {
    			$files->add($link, $personName."_Кандидатская диссертация по теме ".$paper->tema);
    		}
    	}
    	foreach ($person->doctorpapers->getItems() as $paper) {
    		$link = CFileUtils::getLinkAttachment("file_attach", $paper);
    		if (!is_null($link)) {
    			$files->add($link, $personName."_Докторская диссертация по теме ".$paper->tema);
    		}
    	}
    	foreach ($person->degrees->getItems() as $degree) {
    		$link = CFileUtils::getLinkAttachment("file_attach", $degree);
    		$title = "";
    		if (!is_null($degree->degree)) {
    			$title = $degree->degree->getValue();
    		}
    		if (!is_null($link)) {
    			$files->add($link, $personName."_Степень ".$title);
    		}
    	}
    	foreach ($person->portfoliopapers->getItems() as $portfolio) {
    		$link = CFileUtils::getLinkAttachment("file_attach", $portfolio);
    		if (!is_null($link)) {
    			$files->add($link, $personName."_Портфолио ".$portfolio->tema);
    		}
    	}
    	return $files;
    }
    
    /**
     * Количество часов у сотрудника из справочника ставок по должности
     *
     * @param int $postId
     * @param CTerm $year
     * @return int
     */
    public static function getHoursPersonInHoursRateByPost($postId, CTerm $year) {
    	$hours = 0;
    	if ($postId != "") {
    		foreach (CActiveRecordProvider::getWithCondition(TABLE_HOURS_RATE, "dolgnost_id=".$postId." and year_id=".$year->getId())->getItems() as $item) {
    			$hour = new CHoursRate($item);
    			$hours += $hour->rate;
    		}
    	}
    	return $hours;
    }
    
    /**
     * Получить студенческую группу, либо студента, в зависимости от id
     * (используется для добавления шаблона с группой к шаблонам со студентами в архив при массовой печати заявлений на выбор дисциплин)
     * (набор шаблонов formset_students_with_group)
     * 
     * @param int $key
     * @return Ambigous <NULL, CStudentGroup, CStudent>
     */
    public static function getStudentGroupOrStudent($key) {
    	$item = null;
    	$ar = CActiveRecordProvider::getById(TABLE_STUDENT_GROUPS, $key);
    	if (!is_null($ar)) {
    		$item = new CStudentGroup($ar);
    	}
    	if (is_null($item)) {
    		$ar = CActiveRecordProvider::getById(TABLE_STUDENTS, $key);
    		if (!is_null($ar)) {
    			$item = new CStudent($ar);
    		}
    	}
    	return $item;
    }
}