{extends file="_core.3col.tpl"}

{block name="asu_center"}
    <h2>Список публикаций</h2>

    {if ($objects->getCount() == 0)}
        Нет объектов для отображения
    {else}
        <table class="table table-striped table-bordered table-hover table-condensed">
            <thead>
                <tr>
                    <th width="16">&nbsp;</th>
                    <th width="16">#</th>
                    <th width="16">&nbsp;</th>
                    <th>{CHtml::tableOrder("name", $objects->getFirstItem())}</th>
                    <th>{CHtml::tableOrder("page_range", $objects->getFirstItem())}</th>
                    <th>{CHtml::tableOrder("bibliografya", $objects->getFirstItem())}</th>
                    <th>{CHtml::tableOrder("authors_all", $objects->getFirstItem())}</th>
                    <th>В том числе с кафедры</th>
                    <th>{CHtml::tableOrder("grif", $objects->getFirstItem())}</th>
                    <th>{CHtml::tableOrder("publisher", $objects->getFirstItem())}</th>
                    <th>{CHtml::tableOrder("year", $objects->getFirstItem())}</th>
                    <th>{CHtml::tableOrder("approve_date", $objects->getFirstItem())}</th>
                    <th>{CHtml::tableOrder("type_book", $objects->getFirstItem())}</th>
                    <th>{CHtml::tableOrder("copy", $objects->getFirstItem())}</th>
                </tr>
            </thead>
            <tbody>
            {counter start=($paginator->getRecordSet()->getPageSize() * ($paginator->getCurrentPageNumber() - 1)) print=false}
            {foreach $objects->getItems() as $object}
                <tr>
                    <td><a href="#" class="icon-trash" onclick="if (confirm('Действительно удалить публикация')) { location.href='publications.php?action=delete&id={$object->getId()}'; }; return false;"></a></td>
                    <td>{counter}</td>
                    <td><a href="publications.php?action=edit&id={$object->getId()}" class="icon-pencil"></a></td>
                    <td>{$object->name}</td>
                    <td>{$object->page_range}</td>
                    <td>{$object->bibliografya}</td>
                    <td>
                        {$object->authors_all}
                        <ol>
                        {foreach $object->authors->getItems() as $author}
                            <li>{$author->getName()}</li>
                        {/foreach}
                        </ol>
                    </td>
                    <td>
                        {if $object->authors->getCount() > 0}
                            {$object->authors->getCount()}
                        {/if}
                    </td>
                    <td>{$object->grif}</td>
                    <td>{$object->publisher}</td>
                    <td>{$object->year}</td>
                    <td>{$object->approve_date}</td>
                    <td>
                        {if !is_null($object->type)}
                            {$object->type->getValue()}
                        {/if}
                    </td>
                    <td>{CHtml::activeAttachPreview("copy", $object, true)}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>

        {CHtml::paginator($paginator, "publications.php?action=index")}
    {/if}
{/block}

{block name="asu_right"}
    {include file="_staff/publications/common.right.tpl"}
{/block}