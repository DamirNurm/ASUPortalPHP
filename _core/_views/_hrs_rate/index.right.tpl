{if !is_null(CSession::getCurrentUser()->getPersonalSettings())}{if CSession::getCurrentUser()->getPersonalSettings()->isDashboardEnabled()}
    <p>
        <a href="{$web_root}_modules/_dashboard/">
            <center>
                <img src="{$web_root}images/{$icon_theme}/32x32/apps/preferences-system-session.png"><br>
                На рабочий стол
            </center></a>
    </p>
{/if}{/if}

<p>
    <a href="?action=add"><center>
            <img src="{$web_root}images/{$icon_theme}/32x32/actions/list-add.png">
            Добавить ставку
        </center></a>
</p>

<p>
    <a href="{$web_root}s_hours.php"><center>
            <img src="{$web_root}images/{$icon_theme}/32x32/actions/format-justify-fill.png">
            Показатели нагрузки ППС
        </center></a>
</p>