{extends file="_core.component.tpl"}

{block name="asu_center"}
    {if $objects->getCount() == 0}
        Нет объектов для отображения
    {else}
        <table class="table table-striped table-bordered table-hover table-condensed">
            <thead>
            <tr>
            	<th>#</th>
                <th>Семестр</th>
                <th>Вид занятия</th>
                <th>Используемые интерактивные образовательные технологии</th>
                <th>Число часов</th>
            </tr>
            </thead>
            <tbody>
            {foreach $objects->getItems() as $object}
                <tr>
                	<td>{$object->ordering}</td>
                    {if !is_null($object->load->term->corriculum_discipline_section)}
                    	<td>{$object->load->term->corriculum_discipline_section->title}</td>
                    {else}
                    	<td><font color="#FF0000">Обновите значение семестра из дисциплины!</font></td>
                    {/if}
                    <td>{$object->load->loadType}</td>
                    <td><a href="{$web_root}_modules/_corriculum/workplancontent.php?action=editTechnologies&id={$object->getId()}">{$object->technology}</a></td>
                    <td>{$object->value}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {/if}
{/block}

{block name="asu_right"}
    {include file="_corriculum/_workplan/content/common.right.tpl"}
{/block}