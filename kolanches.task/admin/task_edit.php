<?

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/kolanches.task/include.php");



IncludeModuleLangFile(__FILE__);

$ID = intval($_REQUEST["ID"]);



if($REQUEST_METHOD == "POST" && check_bitrix_sessid())
{
    $fields = Array(
        "NAME"        => $_REQUEST[ "NAME"],
        "DESCRIPTION" => $_REQUEST[ "DESCRIPTION"],
        "COMPLETE"    => ($_REQUEST["COMPLETE"] <> "Y" ? "N":"Y")
    );
  
    if ($ID > 0)
    {
        $task = CKolanchesTask::GetByID($ID);

        if (!CKolanchesTask::Update($ID, $fields))
        {
            if (($ex = $APPLICATION->GetException()))
                $errorMessage .= $ex->GetString().". ";
            else
                $errorMessage .= GetMessage("KOL_TASK_EDIT_ERROR").". ";
        }
        
        if($_REQUEST["save"] != '') {
            LocalRedirect(BX_ROOT."/admin/kolanches_task.php?lang=".LANGUAGE_ID);
        }
    }
    else {
        $ID = CKolanchesTask::Add($fields);
        
        if($_REQUEST["save"] != '') {
            LocalRedirect(BX_ROOT."/admin/kolanches_task.php?lang=".LANGUAGE_ID);
        } else if ($_REQUEST["apply"] != '') {
            LocalRedirect(BX_ROOT."/admin/kolanches_task_edit.php?ID=".$ID."&lang=".LANGUAGE_ID);
        }
    }

}



// Получение значений из БД

$str_NAME = "";
$str_DESCRIPTION = "";
$str_COMPLETE = "Y";

if ($ID > 0) {
    $task = CKolanchesTask::GetByID($ID);
    
    $str_NAME = ($task ? htmlspecialcharsbx($task["NAME"]) : "");
    $str_DESCRIPTION = ($task ? htmlspecialcharsbx($task["DESCRIPTION"]) : "");
    $str_COMPLETE = ($task["COMPLETE"] <> "Y" ? "N" :"Y");
    
    if ($str_NAME == "") {
      $ID = 0;
    }
}



$sDocTitle = (GetMessage("MAIN_KOL_TASK_NEW_RECORD"));
$APPLICATION->SetTitle($sDocTitle);



require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
    array(
        "TEXT"=>GetMessage("KOL_TASK_LIST"),
        "TITLE"=>GetMessage("KOL_TASK_LIST_TITLE"),
        "LINK"=>"kolanches_task.php?lang=".LANG,
        "ICON"=>"btn_list",
    )
);
$context = new CAdminContextMenu($aMenu);
$context->Show();



if(is_array($_SESSION["SESS_ADMIN"]["KOL_TASK_EDIT_MESSAGE"]))
{
    CAdminMessage::ShowMessage($_SESSION["SESS_ADMIN"]["KOL_TASK_EDIT_MESSAGE"]);
    $_SESSION["SESS_ADMIN"]["KOL_TASK_EDIT_MESSAGE"]=false;
}

if($message) {
    echo $message->Show();
}



$aTabs = array(
    array("DIV" => "edit1", "TAB" => GetMessage("KOL_TASK_EDIT_TAB_MAIN"), "TITLE"=>GetMessage("KOL_TASK_EDIT_TAB_MAIN_TITLE")),
);
$tabControl = new CAdminForm("rating", $aTabs);
$tabControl->BeginEpilogContent();
?>



<?=bitrix_sessid_post()?>
    <input type="hidden" name="ID" value=<?=$ID?>>
    <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
    <input type="hidden" name="action" value="" id="action">
<?if($_REQUEST["addurl"]<>""):?>
    <input type="hidden" name="addurl" value="<?echo htmlspecialcharsbx($_REQUEST["addurl"])?>">
<?endif;?>
<?
$tabControl->EndEpilogContent();
$tabControl->Begin();

$tabControl->BeginNextFormTab();



$tabControl->AddEditField("NAME", GetMessage('KOL_TASK_EDIT_FRM_NAME'), true, array("size"=>54, "maxlength"=>255), $str_NAME);



$tabControl->BeginCustomField("DESCRIPTION", GetMessage('KOL_TASK_EDIT_FRM_DESCRIPTION'), true);
?>
    <tr class="adm-detail-required-field">
        <td width="40%"><?=GetMessage("KOL_TASK_EDIT_FRM_DESCRIPTION")?></td>
        <td width="60%">
          <textarea name="DESCRIPTION" rows="7" style="width: 405px;"><?=$str_DESCRIPTION?></textarea>
        </td>
    </tr>
<?
$tabControl->EndCustomField("DESCRIPTION");



$tabControl->BeginCustomField("COMPLETE", GetMessage('KOL_TASK_EDIT_FRM_COMPLETE'), false);
?>
    <tr>
        <td><?=GetMessage("KOL_TASK_EDIT_FRM_COMPLETE")?></td>
        <td><?=InputType("checkbox", "COMPLETE", "Y", $str_COMPLETE)?></td>
    </tr>
<?
$tabControl->EndCustomField("COMPLETE");




$tabControl->Buttons(array(
    "disabled"=>false,
    "back_url"=> "kolanches_task.php?lang=".LANG,
));
$tabControl->Show();
$tabControl->ShowWarnings($tabControl->GetName(), $message);



require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>
