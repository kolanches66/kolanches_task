<?
IncludeModuleLangFile(__FILE__);

if($APPLICATION->GetGroupRight("kolanches.task") >= "R")
{
	$aMenu = array(
		"parent_menu" => "global_menu_services",
		"text" => GetMessage("KOL_TASK_MENU_TITLE"),
		"title"=> GetMessage("KOL_TASK_MENU_TITLE"),
		"url" => "kolanches_task.php?lang=".LANGUAGE_ID,
		"more_url" => array("kolanches_task_edit.php"),
	);

	return $aMenu;
}
return false;
