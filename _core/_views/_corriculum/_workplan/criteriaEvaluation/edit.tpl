{extends file="_core.component.tpl"}

{block name="asu_center"}
    <h2>Редактирование критерия оценки</h2>

    {CHtml::helpForCurrentPage()}

    {include file="_corriculum/_workplan/criteriaEvaluation/form.tpl"}
{/block}

{block name="asu_right"}
    {include file="_corriculum/_workplan/criteriaEvaluation/common.right.tpl"}
{/block}