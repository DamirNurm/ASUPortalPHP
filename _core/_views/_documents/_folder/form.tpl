<form action="index.php" method="post" enctype="multipart/form-data" class="form-horizontal">
    {CHtml::hiddenField("action", "save")}
    {CHtml::activeHiddenField("id", $object)}
    {CHtml::activeHiddenField("parent_id", $object)}

    {CHtml::errorSummary($object)}

<div class="control-group">
    {CHtml::activeLabel("title", $object)}
    <div class="controls">
        {CHtml::activeTextField("title", $object)}
        {CHtml::error("title", $object)}
    </div>
</div>

    <div class="control-group">
        <div class="controls">
            {CHtml::submit("Сохранить")}
        </div>
    </div>
</form>