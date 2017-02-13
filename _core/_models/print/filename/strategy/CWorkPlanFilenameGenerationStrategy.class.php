<?php

/**
 * Стратегия генерации имён файлов рабочих программ
 *
 * Class CWorkPlanFilenameGenerationStrategy
 */
class CWorkPlanFilenameGenerationStrategy implements IPrintFilenameGenerationStrategy {
    private $form;
    private $object;

    function __construct(CPrintForm $form, CModel $object){
        $this->form = $form;
        $this->object = $object;
    }


    /**
     * Сгенерировать имя файла
     *
     * @return String
     */
    public function getFilename() {
        $object = $this->object;
        $discipline = "";
        $authors = array();
        if (!is_null($object->authors)) {
            foreach ($object->authors->getItems() as $author) {
                $authors[] = $author->getNameShort();
            }
        }
        if (!CSettingsManager::getSettingValue("template_filename_translit")) {
            $author = implode(", ", $authors);
            $discipline = $object->discipline->getValue();
        } else {
            $author = CUtils::toTranslit(implode(", ", $authors));
            $discipline = CUtils::toTranslit($object->discipline->getValue());
        }
        $filename = $author." - ".$discipline.".odt";
        return $filename;
    }

}