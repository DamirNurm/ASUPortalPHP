{if !is_null(CSession::getCurrentUser()->getPersonalSettings())}{if CSession::getCurrentUser()->getPersonalSettings()->isDashboardEnabled()}
    <p>
        <a href="{$web_root}_modules/_dashboard/">
            <center>
                <img src="{$web_root}images/{$icon_theme}/32x32/apps/preferences-system-session.png"><br>
                На рабочий стол
            </center></a>
    </p>
{/if}{/if}

{if (CSession::isAuth())}
    {if (CSession::getCurrentUser()->hasRole("news_add"))}
        <p>
            <a href="{$web_root}_modules/_news/?action=add">
                <center>
                    <img src="{$web_root}images/{$icon_theme}/32x32/actions/appointment-new.png"><br>
                    Добавить новость
                </center></a>
        </p>
    {/if}
{/if}