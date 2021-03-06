<a href="index.php?action=addBook&discipline_id={CRequest::getInt("id")}">
	<i class="icon-plus">&nbsp;</i>
</a>
{if ($discipline->books->getCount() == 0)}
    Нет учебников для отображения
{else}
<form action="index.php" method="post" id="Books">
    <table class="table table-striped table-bordered table-hover table-condensed">
        <tr>
            <th>&nbsp;</th>
            <th>{CHtml::activeViewGroupSelect("id", $discipline->books->getFirstItem(), true)}</th>
            <th>#</th>
            <th>Название книги</th>
        </tr>
        {counter start=0 print=false}
        {foreach $discipline->books->getItems() as $book}
            <tr>
                <td><a href="#" class="icon-trash" onclick="if (confirm('Действительно удалить книгу?')) { location.href='index.php?action=deleteBooks&id={$book->getId()}&discipline_id={CRequest::getInt("id")}'; }; return false;"></a></td>
                <td>{CHtml::activeViewGroupSelect("id", $book)}</td>
                <td>{counter}</td>
                <td><a href="index.php?action=editBook&id={$book->getId()}&discipline_id={CRequest::getInt("id")}">{$book->book_name}</a></td>
            </tr>
        {/foreach}
    </table>
</form>
{/if}